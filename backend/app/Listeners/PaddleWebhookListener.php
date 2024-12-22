<?php
 
 namespace App\Listeners;

use Exception;
use Illuminate\Http\Request;
use App\Models\PaddleTransactions;
use App\Http\Controllers\Controller;
use Laravel\Paddle\Events\WebhookReceived;
use App\Http\Controllers\Access\AccessHandler;
use App\Http\Controllers\Access\AccessHandling\PaddlePriceHandler;
use App\Http\Controllers\Access\AccessHandling\PaddleSubscriptionHandler;
use App\Http\Controllers\Access\PaddleTransactionHandler;

class PaddleWebhookListener extends Controller
{
    /**
     * Webhookhandling via Paddle Service Provider
     * See Docs: https://developer.paddle.com/webhooks/overview
     * 
     ** Webhook Triggers:
     *  - Completed transaction (ref. payment)
     *  - Canceled / failed transaction
     *  - Updated price (ref. access)
     *  - Updated subscription
     * 
     ** Setup: Webhook App Access Management
     *  1. Initialize app access by Paddle price
     *      - see "\Controllers\Access\PaddlePriceHandler" 
     *  2. Define access token based on the logic:
     *      - Add the token to "\Controllers\Access\AccessHandler.php"
     *  3. Set up the Webhook Gateway in Paddle Cockpit:
     *      > Webhook URL: https://sandbox-vendors.paddle.com/notifications
     *      > Endpoint: https://{URL}/access/webhook
     *          - See "routes\web.php"
     *  4. Add logic according to the access token within the app:
     *      - Set up Middleware to validate feature access:
     *          > Example: "\Middleware\AppAccessCockpit.php"
     *      - Define app logic...
     * 
     * @param WebhookReceived $event The webhook event instance.
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
                $this->cancelUserAccess($contentData);
            }

            else if ($paddleStatus === 'subscription.updated' || $paddleStatus === 'subscription.canceled') {
                $Subscription = new PaddleSubscriptionHandler();
                $Subscription->updateSubscriptionByWebhook($contentData);
            }

            else if ($paddleStatus === 'price.created' || $paddleStatus === 'price.updated') {
                $Price = new PaddlePriceHandler();
                $Price->updatePriceByWebhook($contentData);
            }
        } catch (Exception $e) {
            return;
        }
    }

    /**
     * Initialize user access via transaction webhook.
     * 
     ** Type of transactions:
     *  - "One-Time Purchase": The user pays once.
     *      > User access is granted for a one-time period, either based on expiration date or quantity. 
     *  - "Subscription": The user subscribes to a price, which is billed periodically by Paddle. 
     *      > User access is granted on a recurring basis via webhook, linked to the subscription token and the corresponding user.
     * 
     ** Logic: Client & Server access verification 
     *  1. Fontend: Client Checkout (PaddleJS)
     *      - Users can purchase price via the client, allowing access to specific app features.
     *          > Important: Price must be defined within Paddle
     *          > Important: "Access-Token" must be implemented in app logic
     *  2. Backend: User access initialization
     *      - After client checkout, a "user-access-request" for further verification (Backend, by Webhooks) must be initiated
     *          > Important: This setup allows us to verify user for subsequent webhook calls
     *          > See "\Controllers\Access\UserAccessController::initializeClientCheckout()"
     *  3. Webhook: Verify transactions
     *      - Paddle fires a transaction webhook, after new registered transaction
     *         > Verify user according provided transaction_token or subscription_token
     *              - transaction_token does exists: Price has been purchased very recently by client
     *              - transaction_token does not exists: Transaction must be initialized by a subscription
     *          > Grant access to referred user, according price-access-token
     *              - $access_token: feature access 
     *              - $expiration_period: access limits (deadline)
     *              - $quantity: access limits (amount of something)
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
                    'webhook.subscription.transaction'
                );
            }

            // If transaction is found
            // Entry has been initialized recently through Client and not verified by server yet
            if(!$PaddleTransaction->transaction) return;
            else if($PaddleTransaction->transaction && $PaddleTransaction->subscription_token)
                $PaddleTransaction->createSubscriptionByTransaction('webhook.subscription.verified');

            // Process Webhook
            $PaddleTransaction->completeTransaction('webhook.transaction.completed');

            // Add Access
            AccessHandler::addUserAccess(
                $PaddleTransaction->transaction->user_id,
                $PaddleTransaction->transaction->id,
                $PaddleTransaction->access_token,
                $PaddleTransaction->quantity,
                $PaddleTransaction->expiration_date,
                'webhook.access.granted'
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
     * Remove user access
     *
     * @param array $contentData
     * @return void
     */
    private function cancelUserAccess(array $contentData)
    {
        $PaddleTransaction = new PaddleTransactionHandler(
            PaddleTransactions::where('transaction_token', $contentData['id'])->first()
        );

        AccessHandler::cancelUserAccessByTransaction($PaddleTransaction->transaction);

        $PaddleTransaction->transaction->update([
            'status' => $PaddleTransaction->status,
            'message' => 'webhook.access.removed',
        ]);
    }
}
