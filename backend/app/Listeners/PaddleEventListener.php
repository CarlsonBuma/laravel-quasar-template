<?php
 
namespace App\Listeners;

use Exception;
use App\Models\AccessTransactions;
use App\Http\Controllers\Controller;
use Laravel\Paddle\Events\WebhookReceived;
use App\Http\Controllers\Auth\AppAccess\PaddlePriceHandler;
use App\Http\Controllers\Auth\AppAccess\UserAccessController;
use App\Http\Controllers\Auth\AppAccess\PaddleTransactionHandler;
use App\Http\Controllers\Auth\AppAccess\PaddleSubscriptionHandler;


class PaddleEventListener extends Controller
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
    public function handle(WebhookReceived $event): void
    {
        try {
            $contentData = $event->payload['data'];
            if(!isset($contentData)) return;
            $paddleStatus = $event->payload['event_type'] ?? null;

            if ($paddleStatus === 'transaction.completed') {
                $this->initiateWebhookAccess($contentData);
            } 

            else if ($paddleStatus === 'subscription.updated') {
                $this->updateSubscription($contentData);
            }

            else if ($paddleStatus === 'price.created' || $paddleStatus === 'price.updated') {
                $this->updatePrice($contentData);
            }

            else if ($paddleStatus === 'transaction.payment_failed' || $paddleStatus === 'transaction.canceled') {
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
                AccessTransactions::where([
                    'transaction_token' => $contentData['id']
                ])->first()
            );

            // Already verified
            if($PaddleTransaction->transaction && $PaddleTransaction->transaction->access_added) 
                return;

            // If no transaction is found
            // Webhook is initialized via subscription by some user
            $PaddleTransaction->setTransactionAttributes($contentData);
            if(!$PaddleTransaction->transaction && $PaddleTransaction->subscription_token) 
                $PaddleTransaction->initializeClientTransactionByUserSubscription();

            // If transaction is found
            // Entry has been initialized recently through Client
            if(!$PaddleTransaction->transaction) return;
            else if($PaddleTransaction->transaction && $PaddleTransaction->subscription_token)
                $PaddleTransaction->setSubscription();

            // Process Webhook
            $PaddleTransaction->completeTransaction();

            // Add Access
            $UserAccess = new UserAccessController();
            $UserAccess->addUserAccess(
                $PaddleTransaction->transaction,
                $PaddleTransaction->access_token,
                $PaddleTransaction->quantity,
                $PaddleTransaction->access_token,
                $PaddleTransaction->expiration_date,
                $PaddleTransaction->status
            );
        } catch (Exception $e) {
            $PaddleTransaction->transaction?->update([
                'message' => 'paddle.webhook.error: ' . $e->getMessage()
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
                'message' => 'paddle.webhook.error: ' . $e->getMessage()
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
                'message' => 'paddle.webhook.error: ' . $e->getMessage()
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
            AccessTransactions::where('transaction_token', $contentData['id'])->first()
        );

        $UserAccess = new UserAccessController();
        $UserAccess->removeUserAccess(
            $PaddleTransaction->transaction,
            $PaddleTransaction->status
        );
    }
}
