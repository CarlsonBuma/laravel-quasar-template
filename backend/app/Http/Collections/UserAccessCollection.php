<?php

namespace App\Http\Collections;

use App\Models\PaddleTransactions;
use Illuminate\Database\Eloquent\Collection;

abstract class UserAccessCollection
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