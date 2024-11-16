<?php

namespace App\Http\Controllers\Auth\AppAccess;

use App\Models\Entity;
use App\Models\AccessUsers;
use App\Models\AccessPrices;
use App\Http\Middleware\AppAccess;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Collections\UserAccessCollaction;


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
                return UserAccessCollaction::renderPrice($price);
            });

        return response()->json([
            'prices' => $prices,
            'transactions' => UserAccessCollaction::renderUserTransactions(Auth::id()),
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
}
