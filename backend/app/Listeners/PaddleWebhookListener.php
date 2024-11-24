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
     ** Setup Paddle Price in Cockpit
     * Prices defines user-access, which are set within Paddle Cockpit
     * Make sure, you define price and 'custom_data' accordingly
     *  > 'access_token': Defines app / features access
     *  > 'duration_months': Defines period of current access
     *      > overwritten, by subscription.billing_period.ends_at
     * 
     ** Setup Webhook Gateway:
     * https://sandbox-vendors.paddle.com/notifications
     *  > Endpoint: https://{URL}/access/webhook
     *  > Docs: https://developer.paddle.com/webhooks/overview
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

            // Handle accoring webhook type
            if ($paddleStatus === 'transaction.completed') {
                $this->initiateUserAccess($contentData);
            } 

            else if ($paddleStatus === 'transaction.payment_failed' || $paddleStatus === 'transaction.canceled') {
                $this->cancelUserTransaction($contentData);
            }

            else if ($paddleStatus === 'subscription.updated' || $paddleStatus === 'subscription.canceled') {
                $this->updateSubscription($contentData);
            }

            else if ($paddleStatus === 'price.created' || $paddleStatus === 'price.updated') {
                $this->updatePrice($contentData);
            }

        } catch (Exception $e) {
            return;
        }
    }

    /**
     * Initizalize User Access via Webhook, after transaction.completed
     * Transactions correlate to 
     *  > Payment initialized recently by user client checkout
     *  > New transaction fired by users active subscriptions
     *
     * @param array $contentData
     * @return void
     */
    private function initiateUserAccess(array $contentData)
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
                $PaddleTransaction->createSubscriptionByTransaction('webhook.subscription.verified');

            // Process Webhook
            $PaddleTransaction->completeTransaction('webhook.transaction.verified');

            // Add Access
            AppAccessHandler::addUserAccessByTransaction(
                $PaddleTransaction->transaction,
                $PaddleTransaction->access_token,
                $PaddleTransaction->quantity,
                $PaddleTransaction->expiration_date,
            );

            // Close transaction, after access granted
            $PaddleTransaction->closeTransaction();
        } catch (Exception $e) {
            $PaddleTransaction->transaction?->update([
                'status' => 'failed',
                'message' => 'webhook.access.error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Remove user-access, if transactions failed
     *  > Status 'past_due' is not considered yet!
     *
     * @param array $contentData
     * @return void
     */
    private function cancelUserTransaction(array $contentData)
    {
        $PaddleTransaction = new PaddleTransactionHandler(
            PaddleTransactions::where('transaction_token', $contentData['id'])->first()
        );

        AppAccessHandler::removeUserAccess($PaddleTransaction->transaction);

        $PaddleTransaction->transaction->update([
            'status' => $PaddleTransaction->status,
            'message' => 'user.access.removed',
        ]);
    }

    /**
     * Update subscription changes within Paddle Cockpit
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
     * Update price changes within Paddle Cockpit
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
}
