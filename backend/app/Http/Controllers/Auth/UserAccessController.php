<?php

namespace App\Http\Controllers\Auth;

use Exception;
use GuzzleHttp\Client;
use App\Models\PaddlePrices;
use Illuminate\Http\Request;
use App\Models\PaddleTransactions;
use App\Models\PaddleSubscriptions;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Exception\GuzzleException;
use App\Http\Collections\UserAccessCollection;
use App\Http\Controllers\Auth\AppAccess\AppAccessHandler;
use App\Http\Controllers\Auth\AppAccess\PaddleTransactionHandler;
use App\Http\Controllers\Auth\AppAccess\PaddleSubscriptionHandler;


class UserAccessController extends Controller
{
    /**
     * Load dashboard
     *
     * @return void
     */
    public function loadUserAccess()
    {
        $prices = PaddlePrices::where('is_active', true)
            ->get()
            ->map(function($price) {
                return UserAccessCollection::renderPrice($price, Auth::id());
            });

        return response()->json([
            'prices' => $prices,
            'transactions' => UserAccessCollection::renderUserTransactions(Auth::id()),
            'message' => 'Transactions loaded.',
        ], 200);
    }

    /**
     * Check user access by "$access_token"
     *
     * @param string $access_token
     * @return void
     */
    public function checkUserAccess(string $access_token)
    {
        $userAccess = AppAccessHandler::checkUserAccessByToken(Auth::id(), $access_token);
        return response()->json([
            'access' => $userAccess,
            'access_token' => $access_token,
            'message' => 'Latest access token.',
        ], 200);
    }

    /**
     ** Initialize user's client checkout
     *  > Starting Point of whole user-access process
     *  > "$transaction_token" is required for further validation of client's checkout
     *      > Transaction types: "one-time purchases" or "subscription"-start
     * 
     * Next step: 
     * Wait for verifying client transaction, by webhook
     *  > See WebhookListeners: "/Listeners/PaddleWebhookListener"
     *
     * @param Request $request
     * @return void
     */
    public function initializeClientCheckoutTransaction(Request $request) 
    {
        $data = $request->validate([
            'transaction_token' => ['required', 'string'],
            'customer_token' => ['required', 'string'],
        ]);

        $PaddleTransaction = new PaddleTransactionHandler();
        $PaddleTransaction->initializeUserTransaction(
            Auth::id(), 
            $data['transaction_token'],
            'client.checkout.initialized'
        );

        return response()->json([
            'transaction' => $PaddleTransaction->transaction,
            'message' => 'Checkout initialized.',
        ], 200);
    }

    /**
     * Verify user transaction
     *  > Check if transaction has been already verified by webhook
     *
     * @param Request $request
     * @return void
     */
    public function verifyUserTransaction(Request $request)
    {
        $data = $request->validate([
            'transaction_token' => ['required', 'string'],
        ]);

        $userTransaction = PaddleTransactions::where([
            'user_id' => Auth::id(),
            'transaction_token' => $data['transaction_token']
        ])->first();

        // Verify Request
        if(!$userTransaction) {
            return response()->json([
                'message' => 'Invalid request.',
            ], 422);
        }
        
        // Check if transaction has been verified by webhook
        if($userAccess = AppAccessHandler::checkUserAccessByTransactionID(Auth::id(), $userTransaction->id)) {
            return response()->json([
                'access_token' => $userAccess->access_token,
                'expiration_date' => $userAccess->expiration_date,
                'price_id' => $userTransaction->price_id,
                'message' => 'Transaction validated.',
            ], 200);
        }

        return response()->json([
            'message' => 'Access verification may takes a few seconds.',
        ], 200);
    }

    /**
     * Cancel user subscriptions belonging to the provided price
     *  > Request via paddle api-call
     *  > https://developer.paddle.com/api-reference/subscriptions/cancel-subscription
     * 
     * @param Request $request
     * @return void
     */
    public function cancelSubscription(Request $request)
    {
        $data = $request->validate([
            'price_token' => ['required', 'string'],
        ]);

        try {
            // Verify Price
            $price = PaddlePrices::where('price_token', $data['price_token'])->first();
            if(!$price) throw new Exception('Invalid request.');
            
            // Process all active subscriptions
            $subscriptions = PaddleSubscriptions::where([
                'price_id' => $price->id,
                'user_id' => Auth::id(),
                'canceled_at' => null,
            ])->get();

            // Handle cancleation of subscription(s)
            foreach($subscriptions as $subscription) {
                $PaddleSubscription = new PaddleSubscriptionHandler($subscription);
                if($this->requestCancelSubscription($subscription)) {
                    $subscription->update([
                        'canceled_at' => now(),
                        'status' => 'canceled',
                        'message' => 'client.subscription.canceled'
                    ]);
                }
            }
        } catch (Exception $e) {
            $PaddleSubscription->subscription?->update([
                'message' => 'client.subscription.cancel.error' . $e->getMessage()
            ]);

            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'Subscription canceled.',
        ], 200);
    }

    /**
     * Request cancel subscription via provider api-call
     *  > Incl. Duplicates (why-so-ever)
     *  > 
     *
     * @param object $subscription
     * @return boolean
     */
    private function requestCancelSubscription(object $subscription): bool
    {
        try {

            // Setup client
            $client = new Client([
                'verify' => !(bool) env('PADDLE_SANDBOX'),
                'base_uri' => env('PADDLE_URL'),
            ]);
    
            // Start request
            $response = $client->request('POST', 'subscriptions/' . $subscription->subscription_token . '/cancel', [
                'headers' => [
                    'Authorization' => 'Bearer ' . env('PADDLE_API_KEY'),
                    'Content-Type' => 'application/json'
                ],
                'body' => json_encode([
                    'effective_from' => 'immediately'
                ])
            ]);
           
            // Validate
            if($response->getStatusCode() !== 200) 
                throw new Exception($response->getStatusCode());
            return true;
        } 
        
        // Error by Request
        catch (GuzzleException $e) {
            $subscription->update([
                'message' => 'subscripiton.cancel.request.error: ' . $e->getMessage(),
            ]);
        } 
        
        // Error by Response
        catch (Exception $e) {
            $subscription?->update([
                'message' => 'subscription.cancel.response.error: ' . $e->getMessage(),
            ]);
        }

        return false;
    }
}
