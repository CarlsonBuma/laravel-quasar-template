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
     * Undocumented function
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
     * Undocumented function
     *
     * @param string $access_token
     * @return void
     */
    public function checkUserAccess(string $access_token)
    {
        $subscriptionAccess = AppAccessHandler::checkUserAccessByToken(Auth::id(), $access_token);
        return response()->json([
            'access' => $subscriptionAccess,
            'access_token' => $access_token,
            'message' => 'Latest access token.',
        ], 200);
    }

    /**
     ** Initialize User's Client Checkout
     *  > Starting Point of whole user-access process
     *  > Transaction_Token is required to validate further requests
     *      > A transaction could be a 'one-time-purchases' 
     *      > or the first payment in a ongoing 'subscription'
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
     ** Verify user checkout
     *  > Check if transaction has been already verified by webhook
     *      > See "Listeners/PaddleWebhookListener"
     *  > Verify transaction via Paddle Provider Request
     *      > https://developer.paddle.com/api-reference/transactions/get-transaction 
     *  > Set Subscription Token, if price is type 'subscription'
     *      > Required, to verify future transactions assigned to user-subscription
     *      > as those are not assigned to users anymore, instead to subscription-token
     *          > User subscriptions & access will be handeled via our webhooks!
     *  > Set user access
     *
     * @param Request $request
     * @return void
     */
    public function verifyClientCheckoutTransaction(Request $request)
    {
        $data = $request->validate([
            'transaction_token' => ['required', 'string'],
        ]);

        $PaddleTransaction = new PaddleTransactionHandler(
            PaddleTransactions::where([
                'user_id' => Auth::id(),
                'transaction_token' => $data['transaction_token']
            ])->first()
        );

        // Verify Request
        if(!$PaddleTransaction->transaction) {
            return response()->json([
                'message' => 'Invalid request.',
            ], 422);
        } 
        
        // Check if transaction has been verified already by webhook
        // while client closed checkout
        if(
            $PaddleTransaction->transaction 
            && $userAccess = AppAccessHandler::checkUserAccessByTransactionID(Auth::id(), $PaddleTransaction->transaction->id)
        ) {
            return response()->json([
                'access_token' => $userAccess->access_token,
                'expiration_date' => $userAccess->expiration_date,
                'price_id' => $PaddleTransaction->transaction->price_id,
                'message' => 'Transaction validated.',
            ], 200);
        }

        try {
            // Validate by Request
            $response = $this->validateTransactionByRequest($PaddleTransaction->transaction);
            if(!$response) throw new Exception('Invalid request.');
            
            // Complete user transaction
            $PaddleTransaction->setTransactionAttributes($response['data']);
            $PaddleTransaction->initializeSubscriptionByTransaction('checkout.subscription.verified');
            $PaddleTransaction->completeTransaction('checkout.transaction.verified');
            
            // Set User Access
            if($PaddleTransaction->status === 'completed' || $PaddleTransaction->status === 'paid') {
                AppAccessHandler::addUserAccessByTransaction(
                    $PaddleTransaction->transaction,
                    $PaddleTransaction->access_token,
                    $PaddleTransaction->quantity,
                    $PaddleTransaction->expiration_date,
                );
            }
        } catch (Exception $e) {
            $PaddleTransaction->transaction?->update([
                'message' => 'transaction.checkout.verification.error' . $e->getMessage()
            ]);

            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'access_token' => $PaddleTransaction?->access_token,
            'expiration_date' => $PaddleTransaction?->expiration_date,
            'price_id' => $PaddleTransaction->price_id,
            'message' => 'Transaction verified.',
        ], 200);
    }

    /**
     **Cancel all Subscriptions belonging to the provided Price
     *  > Cancelation must be send to our Payment Provider via API call
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

            // Request cancleation
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
     * Verify Transaction via API Request
     *
     * @return array|null
     */
    private function validateTransactionByRequest(object $transaction): array|null
    {
        try {
            $response = $this->requestPaddleTransactionVerification($transaction->transaction_token);
            if($response->getStatusCode() !== 200) throw new Exception($response->getStatusCode());
            $body = $response->getBody();
            return json_decode($body->getContents(), true);
        } 
        
        // Error by Request
        catch (GuzzleException $e) {
            $transaction->update([
                'message' => 'transaction.verification.request.error: ' . $e->getMessage(),
            ]);
        } 
        
        // Error by Response
        catch (Exception $e) {
            $transaction->update([
                'message' => 'transaction.verification.response.error: ' . $e->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * Verify Transaction-Token, by Paddle API Request
     *
     * @param string $transactionToken
     * @return object $clientResponse
     */
    private function requestPaddleTransactionVerification(string $transactionToken): object
    {
        $client = new Client([
            'verify' => !(bool) env('PADDLE_SANDBOX'),
            'base_uri' => env('PADDLE_URL'),
        ]);

        return $client->request('GET', 'transactions/' . $transactionToken, [
            'headers' => [
                'Authorization' => 'Bearer ' . env('PADDLE_API_KEY')
            ],
        ]);
    }

    /**
     * Request cancel Subscription
     *  > Incl. Duplicates (why-so-ever)
     *
     * @param object|null $subscription
     * @return boolean
     */
    private function requestCancelSubscription(object $subscription = null): bool
    {
        if(!$subscription) return false;
        try {
            $response = $this->requestPaddleSubscriptionCancel($subscription->subscription_token);
            if($response->getStatusCode() !== 200) throw new Exception($response->getStatusCode());
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

    /**
     * Client Request Provider
     * Cancel User Subscription by subscription_id
     *
     * @param string $subscriptionToken
     * @return object|null
     */
    private function requestPaddleSubscriptionCancel(string $subscriptionToken): object
    {
        $client = new Client([
            'verify' => !(bool) env('PADDLE_SANDBOX'),
            'base_uri' => env('PADDLE_URL'),
        ]);

        return $client->request('POST', 'subscriptions/' . $subscriptionToken . '/cancel', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('PADDLE_API_KEY'),
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode([
                'effective_from' => 'immediately'
            ])
        ]);
    }
}
