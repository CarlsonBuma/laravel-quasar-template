<?php

namespace App\Http\Controllers\Auth\AppAccess;

use Exception;
use GuzzleHttp\Client;
use App\Models\AccessPrices;
use Illuminate\Http\Request;
use App\Models\AccessSubscriptions;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Exception\GuzzleException;
use App\Http\Controllers\Auth\AppAccess\PaddleSubscriptionHandler;


class UserSubscriptionController extends Controller
{
    /**
     ** Cancel all Subscriptions belonging to the provided Price
     *  Cancelation must be send via our Payment Provider thorugh API call
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
            $price = AccessPrices::where('price_token', $data['price_token'])->first();
            if(!$price) throw new Exception('Invalid request.');
            
            // Process all active subscriptions
            $subscriptions = AccessSubscriptions::where([
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
                        'status' => 'subscription.canceled',
                        'message' => 'client.subscription.canceled'
                    ]);
                }
            }
        } catch (Exception $e) {
            $PaddleSubscription->subscription?->update([
                'message' => 'cancelation.error' . $e->getMessage()
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
     ** Request cancel Subscription
     *
     * @param string $subscriptionToken
     * @return bool
     */
    /**
     ** Request cancel Subscription
     *  Incl. Duplicates (why-so-ever)
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
                'message' => 'subscription.request.error: ' . $e->getMessage(),
            ]);
        } 
        
        // Error by Response
        catch (Exception $e) {
            $subscription?->update([
                'message' => 'subscription.response.error: ' . $e->getMessage(),
            ]);
        }

        return false;
    }

    /**
     **Client Request Provider
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
