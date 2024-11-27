<?php

namespace App\Http\Controllers\Access;

use App\Models\UserAccess;


class AccessHandler
{
    /**
     * Add user app-access, by provided '$access_token'
     *  > Flag $access_token: Defines access to app-features
     *  > Flag $quantity: Undefined (add logic)
     *  > Flag $expiration_date: End of access
     *
     * @param integer $userID
     * @param integer|null $transactionID
     * @param string $accessToken
     * @param integer $quantity
     * @param string $expirationDate
     * @param string $message
     * @return object
     */
    static public function addUserAccess(int $userID, int $transactionID = null, string $accessToken, int $quantity, string $expirationDate, string $message): object
    {
        return UserAccess::create([
            'user_id' => $userID,
            'transaction_id' => $transactionID,
            'access_token' => $accessToken,
            'quantity' => $quantity,
            'expiration_date' => $expirationDate,
            'is_active' => true,
            'status' => 'access.granted',
            'message' => $message
        ]);
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
     * Get all active accesses, unified by access_token
     *  > Entry with latest expiration_date
     *
     * @param integer $userID
     * @return object
     */
    static public function getLatestUserAccesses(int $userID): object
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
