<?php

namespace App\Http\Controllers\Auth\AppAccess;

use App\Models\UserAccess;


class AppAccessHandler
{
    /**
     * Get latest active subscription of User
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
     * Get latest active subscription of User
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

        $transaction->update([
            'access_added' => true,
            'is_verified' => true,
            'status' => 'completed',
            'message' => 'user.access.granted'
        ]);
    }

    /**
     * Remove user-app-access
     *
     * @param object $transaction
     * @param string $status
     * @return void
     */
    static public function removeUserAccess(object $transaction, string $status = 'undefined'): void
    {
        UserAccess::where([
            'user_id' => $transaction->user_id,
            'transaction_id' => $transaction->id,
        ])->update([
            'is_active' => false
        ]);

        $transaction->update([
            'status' => $status,
            'message' => 'user.access.removed',
        ]);
    }
}
