<?php

namespace App\Http\Controllers\Access;

use App\Models\AccessPrices;
use App\Http\Middleware\AccessUser;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Collections\AccessCollection;


class UserAccessController extends Controller
{

    //* Check current Access
    public function checkUserAccess(string $access_token)
    {
        $subscriptionAccess = AccessUser::checkUserAccessByToken(Auth::id(), $access_token);
        return response()->json([
            'access' => $subscriptionAccess,
            'access_token' => $access_token,
            'message' => 'Latest access token.',
        ], 200);
    }

    //* Load Access
    public function loadUserAccess()
    {
        $prices = AccessPrices::where('is_active', true)
            ->get()
            ->map(function($price) {
                return AccessCollection::renderPrice($price);
            });

        $transactions = AccessCollection::renderUserTransactions(Auth::id());

        return response()->json([
            'prices' => $prices,
            'transactions' => $transactions,
            'message' => 'Transactions loaded.',
        ], 200);
    }
}
