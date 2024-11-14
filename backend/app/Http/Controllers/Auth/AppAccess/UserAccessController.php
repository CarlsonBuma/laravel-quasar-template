<?php

namespace App\Http\Controllers\Auth\AppAccess;

use App\Models\Entity;
use App\Models\AccessUsers;
use App\Models\AccessPrices;
use App\Http\Middleware\AppAccess;
use App\Models\AccessTransactions;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;


class UserAccessController extends Controller
{
    /**
     * Undocumented function
     *
     * @param string $access_token
     * @return void
     */
    public function checkUserAccess(string $access_token)
    {
        $subscriptionAccess = AppAccess::checkUserAccessByToken(Auth::id(), $access_token);
        return response()->json([
            'access' => $subscriptionAccess,
            'access_token' => $access_token,
            'message' => 'Latest access token.',
        ], 200);
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function loadUserAccess()
    {
        $prices = AccessPrices::where('is_active', true)
            ->get()
            ->map(function($price) {
                return $this->renderPrice($price);
            });

        $transactions = $this->renderUserTransactions(Auth::id());

        return response()->json([
            'prices' => $prices,
            'transactions' => $transactions,
            'message' => 'Transactions loaded.',
        ], 200);
    }

    /**
     * Add user-app-access
     *  > by provided Flag: $access_token
     *
     * @param object $transaction
     * @param string $accessToken
     * @param integer $quantity
     * @param string $expirationDate
     * @param string $status
     * @return void
     */
    public function addUserAccess(object $transaction, string $accessToken, int $quantity, string $expirationDate, string $status = 'access.granted'): void
    {
        if(!$transaction) throw 'error.no.transaction.provided';
        $userEntity = Entity::where('user_id', $transaction->user_id)->first();
        AccessUsers::create([
            'transaction_id' => $transaction->id,
            'user_id' => $transaction->user_id,
            'entity_id' => $userEntity?->id, 
            'access_token' => $accessToken,
            'quantity' => $quantity,
            'expiration_date' => $expirationDate,
            'is_active' => true,
        ]);

        $transaction->update([
            'access_added' => true,
            'is_verified' => true,
            'status' => $status,
            'message' => 'access.granted'
        ]);
    }

    /**
     * Remove user-app-access
     *
     * @param object $transaction
     * @param string $status
     * @return void
     */
    public function removeUserAccess(object $transaction, string $status = 'access.removed'): void
    {
        if(!$transaction) return;
        AccessUsers::where([
            'user_id' => $transaction->user_id,
            'transaction_id' => $transaction->id,
        ])->update([
            'is_active' => false
        ]);

        $transaction->update([
            'status' => $status,
            'message' => 'access.removed',
        ]);
    }

    /**
     * Undocumented function
     *
     * @param integer $userID
     * @return object|null
     */
    private function renderUserTransactions(int $userID): ?object
    {
        return AccessTransactions::where([
            'access_transactions.user_id' => $userID,
        ])
        ->join('access_prices', 'access_prices.id', '=', 'access_transactions.price_id')
        ->join('access_users', 'access_users.transaction_id', '=', 'access_transactions.id')
        ->select(
            'access_prices.name', 'access_prices.price_token',
            'access_users.expiration_date', 'access_users.is_active', 
            'access_transactions.transaction_token', 'access_transactions.price_id', 'access_transactions.quantity',
            'access_transactions.total', 'access_transactions.tax', 'access_transactions.currency_code', 'access_transactions.status',  'access_transactions.created_at'
        )
        ->orderBy('access_users.is_active', 'desc')
        ->orderBy('access_users.expiration_date', 'desc')
        ->get();
    }

    /**
     * Undocumented function
     *
     * @param object|null $price
     * @return array|null
     */
    private function renderPrice(object|null $price): ?array 
    {
        if(!$price) return null;
        $subscription = $price->has_subscriptions()->where('canceled_at', null)->first();
        return [
            'id' => $price->id,
            'price_token' => $price->price_token,
            'name' => $price->name,
            'type' => $price->type,
            'price' => $price->price,
            'currency_code' => $price->currency_code,
            'billing_interval' => $price->billing_interval,
            'billing_frequency' => $price->billing_frequency,
            'trial_interval' => $price->trial_interval,
            'trial_frequency' => $price->trial_frequency,
            'duration_months' => $price->duration_months,
            'has_subscription' => $subscription ? true : false,
        ];
    }
}
