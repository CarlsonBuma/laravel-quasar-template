<?php

namespace App\Http\Controllers\Auth;

use App\Models\UserAccess;
use App\Models\PaddlePrices;
use App\Http\Controllers\Auth\AppAccess\AppAccessHandler;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Collections\UserAccessCollection;


class UserAccessController extends Controller
{
    /**
     * Undocumented function
     *
     * @return void
     */
    public function loadUserAccess()
    {
        $prices = PaddlePrices::where('is_active', true)
            ->get()
            ->map(function($price) {
                return UserAccessCollection::renderPrice($price, Auth::id());
            });

        return response()->json([
            'prices' => $prices,
            'transactions' => UserAccessCollection::renderUserTransactions(Auth::id()),
            'message' => 'Transactions loaded.',
        ], 200);
    }

    /**
     * Undocumented function
     *
     * @param string $access_token
     * @return void
     */
    public function checkUserAccess(string $access_token)
    {
        $subscriptionAccess = AppAccessHandler::checkUserAccessByToken(Auth::id(), $access_token);
        return response()->json([
            'access' => $subscriptionAccess,
            'access_token' => $access_token,
            'message' => 'Latest access token.',
        ], 200);
    }
}
