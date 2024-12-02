<?php
 
 namespace App\Listeners;

use Exception;
use Illuminate\Http\Request;
use App\Models\PaddleTransactions;
use App\Http\Controllers\Controller;
use Laravel\Paddle\Events\WebhookReceived;
use App\Http\Controllers\Access\AccessHandler;
use App\Http\Controllers\Access\PaddlePriceHandler;
use App\Http\Controllers\Access\PaddleTransactionHandler;
use App\Http\Controllers\Access\PaddleSubscriptionHandler;


class PaddleWebhookListener extends Controller
{
    /**
     ** Webhookhandling via Paddle Service Provider
     * 
     *  
     * These features are defined by the price token and expiration period, which are configured 
     * within the Paddle Cockpit. 
     * 
     ** Call
     * User purchase access token from our provided products (ref. Paddle Prices). 
     * s can buy tokens at a specified price to access various app features via our Paddle Provider.
     * Triggers: Paddle events (see https://developer.paddle.com/webhooks/overview)
     *  - Paddle transaction has been initialized by one-time purchase or subscription
     *  - Price has been updated 
     *  - Subscription updated (incl. cancelation)
     * 
     ** Action
     * 
     * 
     * 
     ** Goal
     * By handling webhooks accordingly, access tokens will be granted to users after purchasing Prices
     * privided via Paddle Price Cockpit.
     *  - User can buy price via PaddleJS UI
     *  - After successfull initialization of transaction, webhooks are verifying user's access
     * 
     * 
     * 
     * **Definition:
     * User can gain access to provided app features, according to prices and its token.
     * Access tokens are issued to user, after successfull verification of transactions automatically.
     * 
     * **Call:
     * Paddle Event: see https://developer.paddle.com/webhooks/overview
     *  - Paddle transaction has been initialized by one-time purchase or subscription
     *  - Price has been updated 
     *  - Subscription updated (incl. cancelation)
     * 
     * **Action:
     * Handling webhook according event
     *  - Price: Price been updated
     *  - Subscription: User subscription has been updated / canceled 
     *  - Transaction: Issue access token to user, after successfull verification of transaction.
     *      - User gains access to provided app features, according flags: 
     *          - $access_token: feature definition 
     *          - $expiration_period: access definition (deadline)
     *          - $quantity: access definition (amount of something)
     *  
     ** Restrictions:
     *  - Access requests has been already initialized after successful client checkout via PaddleJS (UI-REST API call).
     *      - User request access via Client to a provided token, that allows him using certain app features.
     *          - See: "\Controllers\Access\UserAccessController.php" as initial client access request     
     *      - This setup allows us to verify users for subsequent webhook calls.
     * 
     ** Dependencies:
     *  - See: "\Controllers\Access\UserAccessController.php" as initial client access request
     *  - Paddle Cockpit: Our webhooks correspond to its correct configuration within price setup.
     *      1. Ensure including 'custom_data' in price configuration:
     *          > 'access_token' (required): Defines access to the app and its features.
     *          > 'duration_months': Defines the period of access.
     *              > Note: This is overridden by the subscription.billing_period.ends_at value
     *      3. Define access token based on the logic:
     *          > Add token to "\Controllers\Access\AccessHandler.php"
     *          > Enable the current token within "\Controllers\Access\PaddlePriceHandler.php".
     *      4. Setup Webhook Gateway:
     *          > Webhook URL: https://sandbox-vendors.paddle.com/notifications
     *          > Endpoint: https://{URL}/access/webhook    
     *      5. Add logic according to access tokens within app.
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
     ** Definition
     * Initialize user access via webhook after transaction completion (transaction.completed).
     * 
     ** Defintion
     * Types of transactions:
     *  - "One-Time Purchase": The user pays once.
     *      - User access is granted for a one-time period, either based on expiration date or quantity. 
     *  - "Subscription": The user subscribes to a price, which is billed periodically by the payment provider. 
     *      - User access is granted on a recurring basis via webhook, linked to the subscription token and the corresponding user.
     * 
     ** Call
     * Transactions are triggered by:
     *  > A transaction initiated by the user client during payment checkout.
     *  > A new transaction generated by an active user subscription.
     *
     ** Action
     *  - Verify transaction according privided transaction_token or subscription_token
     *  - Grant access to referred user, according price and its access token 
     *
     ** Restrictions
     *  - User verified by transaction_token or subscription_token
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
     * Remove user access, 
     * if transactions failed
     *
     * @param array $contentData
     * @return void
     */
    private function cancelUserTransaction(array $contentData)
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

    /**
     * Update subscription changes that have 
     * been triggered within Paddle Cockpit
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
     * Update price changes that have 
     * been triggered within Paddle Cockpit
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
