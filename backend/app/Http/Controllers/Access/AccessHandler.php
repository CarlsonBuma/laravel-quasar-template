<?php

namespace App\Http\Controllers\Access;

use App\Models\UserAccess;

class AccessHandler
{
    /**
     * User Access Management
     * Defines user access within our app
     *  > Public access can be purchased by users themself (ref. app-prices)
     *      - User access will be initiated by provided prices and its access token
     *      - Important: Allow access token within app!
     *          - see "\Access\PaddlePriceHandler"
     *  > Private access can be definied within app (eg. access-admin)
     *      - Access must be initiated to a user manually (eg. by admin)
     * 
     * ...........................................
     * . Define own access tokens here
     * ...........................................
     */
    public static $tokenAdmin = 'access-admin';
    public static $tokenCockpit = 'access-cockpit';

    /**
     * Add user app access
     *
     * @param integer $userID
     * @param integer|null $transactionID
     * @param string $accessToken, defines access to app-features
     * @param integer $quantity, amount of something (add logic)
     * @param string $expirationDate, end of access
     * @param string $message
     * @return object
     */
    static public function addUserAccess(int $userID, int $transactionID = null, string $accessToken = 'undefined', int $quantity = 0, string $expirationDate, string $message): object
    {
        return UserAccess::updateOrCreate([
                'user_id' => $userID,
                'access_token' => $accessToken
            ], [
                'transaction_id' => $transactionID,
                'quantity' => $quantity,
                'expiration_date' => $expirationDate,
                'is_active' => true,
                'status' => 'access.granted',
                'message' => $message
            ]
        );
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
            ])->whereDate('expiration_date', '>=', date('Y-m-d'))
            ->latest('expiration_date')
            ->first();
    }

    /**
     * Get latest access, by access token
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
            ])->whereDate('expiration_date', '>=', date('Y-m-d'))
            ->latest('expiration_date')
            ->first();
    }

    /**
     * Get latest access, by transaction_id
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
            ])->whereDate('expiration_date', '>=', date('Y-m-d'))
            ->latest('expiration_date')
            ->first();
    }

    /**
     * Get all active accesses by latest expiration_date
     * Unified by access_token
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
            ])->whereDate('expiration_date', '>=', date('Y-m-d'))
            ->orderBy('access_token')
            ->orderBy('expiration_date', 'desc')
            ->distinct('access_token')
            ->get();
    }

    /**
     * Remove user app access by transaction
     *
     * @param object $transaction
     * @return void
     */
    static public function cancelUserAccessByTransaction(object $transaction, string $status, string $message): void
    {
        UserAccess::where([
            'user_id' => $transaction->user_id,
            'transaction_id' => $transaction->id,
        ])->update([
            'is_active' => false,
            'status' => $status,
            'message' => $message
        ]);
    }
}
