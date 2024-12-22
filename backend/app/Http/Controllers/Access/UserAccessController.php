<?php

namespace App\Http\Controllers\Access;

use Exception;
use GuzzleHttp\Client;
use App\Models\UserAccess;
use App\Models\PaddlePrices;
use Illuminate\Http\Request;
use App\Models\PaddleTransactions;
use App\Models\PaddleSubscriptions;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Collections\UserCollection;
use GuzzleHttp\Exception\GuzzleException;
use App\Http\Collections\AccessCollection;
use App\Http\Controllers\Access\AccessHandler;
use App\Http\Controllers\Access\PaddleTransactionHandler;

class UserAccessController extends Controller
{
    /**
     * Load prices, transactions and access
     *
     * @return void
     */
    public function loadUserAccess()
    {
        $prices = PaddlePrices::where('is_active', true)
            ->where('status', 'active')
            ->get()
            ->map(function($price) {
                return AccessCollection::renderUserPrice($price, Auth::id());
            });

        $userAccess = UserAccess::where('user_id', Auth::id())
            ->orderBy('expiration_date', 'desc')
            ->get()
            ->map(function($access) {
                return AccessCollection::renderUserAccess($access);
            });

        $userTransactions = PaddleTransactions::where('user_id', Auth::id())
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function($transaction) {
                return AccessCollection::renderUserTransaction($transaction);
            });

        return response()->json([
            'prices' => $prices,
            'access' => $userAccess,
            'transactions' => $userTransactions,
            'message' => 'Transactions loaded.',
        ], 200);
    }

    /**
     * Check user access by "access_token"
     *
     * @param string $access_token
     * @return void
     */
    public function checkUserAccess(string $access_token)
    {
        $userAccess = AccessHandler::checkUserAccessByToken(Auth::id(), $access_token);
        return response()->json([
            'access' => $userAccess,
            'access_token' => $access_token,
            'message' => 'Latest access token.',
        ], 200);
    }

    /**
     * Initialize user's client checkout.
     * This marks the starting point of the entire user access verification process:
     *  > A "transaction_token" is assigned to the user for subsequent webhook verifications
     *  > Further verification will be handled by "/Listeners/PaddleWebhookListener"
     * 
     * @param Request $request
     * @return void
     */
    public function initializeClientCheckout(Request $request) 
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
     * Verify user transaction by "transaction_token"
     * Check if transaction has been already verified by webhook successfully
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
        if($userAccess = AccessHandler::checkUserAccessByTransactionID(Auth::id(), $userTransaction->id)) {
            return response()->json([
                'access' => UserCollection::render_user_access($userAccess),
                'price_id' => $userTransaction->price_id,
                'message' => 'Access granted.',
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
        $userSubscription = null;
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
                $userSubscription = $subscription;
                if($this->requestCancelSubscription($subscription)) {
                    $subscription->update([
                        'canceled_at' => now(),
                        'status' => 'canceled',
                        'message' => 'client.subscription.canceled'
                    ]);
                }
            }
        } catch (Exception $e) {
            $userSubscription?->update([
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
