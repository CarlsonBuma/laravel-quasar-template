<?php

namespace App\Http\Collections;

use App\Http\Controllers\Auth\AppAccess\AppAccessHandler;
use App\Models\PaddleTransactions;
use App\Models\PaddleSubscriptions;
use Illuminate\Database\Eloquent\Collection;

abstract class UserAccessCollection
{
    /**
     * Get price
     *  > Check for active user subscriptions in current price
     *
     * @param object $price
     * @param integer $userID
     * @return array
     */
    static public function renderPrice(object $price, int $userID): array
    {
        return [
            '_type' => 'Collection $price',
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
            'access_token' => $price->access_token,
            'has_access' => AppAccessHandler::checkUserAccessByToken($userID, $price->access_token),
            'is_subscription' => $price->trial_interval && $price->trial_frequency,
            'has_active_subscription' => PaddleSubscriptions::where([
                    'user_id' => $userID,
                    'price_id' => $price->id,
                    'canceled_at' => null,
                ])->exists()
        ];
    }

    /**
     * Render transactions
     *
     * @param integer $userID
     * @return Collection
     */
    static public function renderUserTransactions(int $userID): Collection
    {
        return PaddleTransactions::where('paddle_transactions.user_id', $userID)
            ->join('paddle_prices', 'paddle_prices.id', '=', 'paddle_transactions.price_id')
            ->join('user_access', 'user_access.transaction_id', '=', 'paddle_transactions.id')
            ->select(
                'paddle_prices.name', 'paddle_prices.price_token',
                'user_access.expiration_date', 'user_access.is_active', 
                'paddle_transactions.transaction_token', 'paddle_transactions.price_id', 'paddle_transactions.quantity',
                'paddle_transactions.total', 'paddle_transactions.tax', 'paddle_transactions.currency_code', 'paddle_transactions.status',  'paddle_transactions.created_at'
            )
            ->orderBy('user_access.is_active', 'desc')
            ->orderBy('user_access.expiration_date', 'desc')
            ->get();
    }
}