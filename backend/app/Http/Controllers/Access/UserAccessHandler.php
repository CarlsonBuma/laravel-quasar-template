<?php

namespace App\Http\Controllers\Access;

use App\Models\UserAccess;


class UserAccessHandler
{
    /**
     * Get all active accesses, unified by access_token
     *  > Entry with latest expiration_date
     *
     * @param integer $userID
     * @return object
     */
    static public function getLatestActiveAccesses(int $userID): object
    {
        return UserAccess::select('user_access.*')
            ->where([
                'user_id' => $userID,
                'is_active' => true,
            ])
            ->where('quantity', '>=', 1)
            ->whereDate('expiration_date', '>=', date('Y-m-d'))
            ->orderBy('access_token')
            ->orderBy('expiration_date', 'desc')
            ->distinct('access_token')
            ->get();
    }

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
    static public function cancelUserAccessByTransaction(object $transaction): void
    {
        UserAccess::where([
            'user_id' => $transaction->user_id,
            'transaction_id' => $transaction->id,
        ])->update([
            'is_active' => false
        ]);
    }
}
