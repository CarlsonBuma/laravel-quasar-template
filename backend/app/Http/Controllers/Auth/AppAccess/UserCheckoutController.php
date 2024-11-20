<?php

namespace App\Http\Controllers\Auth\AppAccess;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\AppAccess\AppAccessHandler;
use App\Models\PaddleTransactions;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Exception\GuzzleException;
use App\Http\Controllers\Auth\AppAccess\PaddleTransactionHandler;


/**
 ** Setup Payment Gateway
 * https://sandbox-vendors.paddle.com/
 * 
 ** Documentation
 * https://developer.paddle.com/api-reference/overview
 * 
 */
class UserCheckoutController extends Controller
{
    /**
     * Initialize User's Client Checkout
     *  > Starting Point of whole user-access
     *  > Transaction_Token is required to validate purchase
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
     ** Verify checkout
     *  > Request transaction via Provider API
     *  > Add access, if successfull
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
        
        // Check if transaction has been verified already by Providers Webhook
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
     ** Verify Transaction via API Request
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
     ** Verify Transaction-Token
     ** by Paddle API Request
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
}
