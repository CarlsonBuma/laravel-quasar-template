<?php

namespace App\Http\Middleware;

use App\Models\AccessUsers;


class AppAccess
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
        return AccessUsers::where([
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
        return AccessUsers::where([
                'user_id' => $userID,
                'transaction_id' => $transactionID,
                'is_active' => true,
            ])->where('quantity', '>=', 1)
            ->whereDate('expiration_date', '>=', date('Y-m-d'))
            ->latest('expiration_date')
            ->first();
    }
}
