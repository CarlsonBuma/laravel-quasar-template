<?php

namespace App\Http\Controllers\Auth\AppAccess;

use App\Models\UserAccess;


class AppAccessHandler
{
    /**
     * Check current access
     *
     * @param integer $accessID
     * @return object|null
     */
    static public function checkAccessByID(int $accessID): ?object
    {
        return UserAccess::where([
                'id' => $accessID,
                'is_active' => true
            ])->where('quantity', '>=', 1)
            ->whereDate('expiration_date', '>=', date('Y-m-d'))
            ->latest('expiration_date')
            ->first();
    }

    /**
     * Get latest access, by '$accessToken'
     *
     * @param integer $userID
     * @param string $accessToken
     * @return object|null
     */
    static public function checkUserAccessByToken(int $userID, string $accessToken): ?object
    {
        if(!$accessToken) return null;
        return UserAccess::where([
                'user_id' => $userID,
                'access_token' => $accessToken,
                'is_active' => true
            ])->where('quantity', '>=', 1)
            ->whereDate('expiration_date', '>=', date('Y-m-d'))
            ->latest('expiration_date')
            ->first();
    }

    /**
     * Get latest access, by '$transactionID'
     *
     * @param integer $userID
     * @param integer $transactionID
     * @return object|null
     */
    static public function checkUserAccessByTransactionID(int $userID, int $transactionID): ?object
    {
        return UserAccess::where([
                'user_id' => $userID,
                'transaction_id' => $transactionID,
                'is_active' => true,
            ])->where('quantity', '>=', 1)
            ->whereDate('expiration_date', '>=', date('Y-m-d'))
            ->latest('expiration_date')
            ->first();
    }

    /**
     * Add user app-access, by provided '$access_token'
     *  > Flag $access_token: Defines access to app-features
     *  > Flag $quantity: Undefined (add logic)
     *  > Flag $expiration_date: End of access
     *
     * @param object $transaction
     * @param string $accessToken
     * @param integer $quantity
     * @param string $expirationDate
     * @return void
     */
    static public function addUserAccessByTransaction(object $transaction, string $accessToken, int $quantity, string $expirationDate): void
    {
        UserAccess::create([
            'transaction_id' => $transaction->id,
            'user_id' => $transaction->user_id,
            'access_token' => $accessToken,
            'quantity' => $quantity,
            'expiration_date' => $expirationDate,
            'is_active' => true,
        ]);
    }

    /**
     * Remove user app-access
     *
     * @param object $transaction
     * @return void
     */
    static public function removeUserAccess(object $transaction): void
    {
        UserAccess::where([
            'user_id' => $transaction->user_id,
            'transaction_id' => $transaction->id,
        ])->update([
            'is_active' => false
        ]);
    }
}
