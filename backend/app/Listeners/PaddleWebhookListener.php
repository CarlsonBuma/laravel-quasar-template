<?php
 
 namespace App\Listeners;

use Exception;
use Illuminate\Http\Request;
use App\Models\PaddleTransactions;
use App\Http\Controllers\Controller;
use Laravel\Paddle\Events\WebhookReceived;
use App\Http\Controllers\Auth\AppAccess\AppAccessHandler;
use App\Http\Controllers\Auth\AppAccess\PaddlePriceHandler;
use App\Http\Controllers\Auth\AppAccess\PaddleTransactionHandler;
use App\Http\Controllers\Auth\AppAccess\PaddleSubscriptionHandler;


class PaddleWebhookListener extends Controller
{
    /**
     ** Setup Webhook Gateway:
     * https://sandbox-vendors.paddle.com/notifications
     * 
     ** Documentation:
     * https://developer.paddle.com/webhooks/overview
     * 
     ** Webhook:
     * https://{URL}/paddle/webhook
     *
     * @param WebhookReceived $event
     * @return void
     */
    public function handleWebhook(Request $request): void
    {
        try {

            // Prepare
            $payload = $request->json()->all();
            $contentData = $payload['data'];
            if(!isset($contentData)) return;
            $paddleStatus = $payload['event_type'] ?? null;

            // Features
            if ($paddleStatus === 'transaction.completed') {
                $this->initiateWebhookAccess($contentData);
            } 

            else if ($paddleStatus === 'subscription.updated' || $paddleStatus === 'subscription.canceled') {
                $this->updateSubscription($contentData);
            }

            else if ($paddleStatus === 'price.created' || $paddleStatus === 'price.updated') {
                $this->updatePrice($contentData);
            }

            else if ($paddleStatus === 'transaction.payment_failed' || $paddleStatus === 'transaction.canceled' || $paddleStatus === 'transaction.past_due') {
                $this->removeUserAcccess($contentData);
            }
        } catch (Exception $e) {
            return;
        }
    }

    /**
     * Initizalize User Access via Webhook, after new payment completet!
     *      > Transaction Token: payment by user-client
     *      > Subscription Token: payment by user-subscription
     *
     * @param array $contentData
     * @return void
     */
    private function initiateWebhookAccess(array $contentData)
    {
        try {
            $PaddleTransaction = new PaddleTransactionHandler(
                PaddleTransactions::where([
                    'transaction_token' => $contentData['id']
                ])->first()
            );

            // Already verified
            if($PaddleTransaction->transaction?->access_added) 
                return;

            // If no transaction is found
            // Webhook is initialized via subscription by some user
            $PaddleTransaction->setTransactionAttributes($contentData);
            if(!$PaddleTransaction->transaction && $PaddleTransaction->subscription_token) {
                $PaddleTransaction->initializeUserTransactionBySubscription(
                    $PaddleTransaction->subscription_token,
                    'webhook.subscription.transaction.verified'
                );
            }

            // If transaction is found
            // Entry has been initialized recently through Client and not verified by server yet
            if(!$PaddleTransaction->transaction) return;
            else if($PaddleTransaction->transaction && $PaddleTransaction->subscription_token)
                $PaddleTransaction->initializeSubscriptionByTransaction('webhook.subscription.verified');

            // Process Webhook
            $PaddleTransaction->completeTransaction('webhook.transaction.verified');

            // Add Access
            AppAccessHandler::addUserAccessByTransaction(
                $PaddleTransaction->transaction,
                $PaddleTransaction->access_token,
                $PaddleTransaction->quantity,
                $PaddleTransaction->expiration_date,
            );
        } catch (Exception $e) {
            $PaddleTransaction->transaction?->update([
                'status' => 'failed',
                'message' => 'webhook.access.error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Update Subscription Credentials, by Webhook
     *
     * @param array $contentData
     * @return void
     */
    private function updateSubscription(array $contentData)
    {
        try {
            $PaddleSubscription = new PaddleSubscriptionHandler();
            $PaddleSubscription->updateSubscriptionByWebhook($contentData);
        } catch (Exception $e) {
            $PaddleSubscription->subscription?->update([
                'status' => 'failed',
                'message' => 'webhook.subscription.error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Update Price, by Webhook
     *
     * @param array $contentData
     * @return void
     */
    private function updatePrice(array $contentData)
    {
        try {
            $PaddlePrice = new PaddlePriceHandler();
            $PaddlePrice->updatePriceByWebhook($contentData);
        } catch (Exception $e) {
            $PaddlePrice->price?->update([
                'status' => 'failed',
                'message' => 'webhook.price.error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Remove User Access, transaction_Token by Webhook
     *
     * @param array $contentData
     * @return void
     */
    private function removeUserAcccess(array $contentData)
    {
        $PaddleTransaction = new PaddleTransactionHandler(
            PaddleTransactions::where('transaction_token', $contentData['id'])->first()
        );

        AppAccessHandler::removeUserAccess(
            $PaddleTransaction->transaction,
            $PaddleTransaction->status
        );
    }
}
