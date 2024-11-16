<?php

namespace App\Http\Collections;

use App\Models\AccessTransactions;


abstract class UserAccessCollaction
{
    /**
     * Undocumented function
     *
     * @param object|null $price
     * @return array
     */
    static public function renderPrice(object $price = null): array
    {
        if(!$price) return [];
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
            'is_subscription' => $price->has_subscriptions->exists(),
        ];
    }

    /**
     * Undocumented function
     *
     * @param integer $userID
     * @return array
     */
    static public function renderUserTransactions(int $userID): array
    {
        return AccessTransactions::where('access_transactions.user_id', $userID)
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
}