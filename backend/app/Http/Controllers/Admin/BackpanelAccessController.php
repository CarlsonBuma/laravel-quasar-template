<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\PaddlePrices;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Collections\AccessCollection;
use App\Models\PaddleTransactions;
use App\Models\UserAccess;

class BackpanelAccessController extends Controller
{
    /**
     * Undocumented function
     *
     * @return void
     */
    public function loadPrices()
    {
        $prices = PaddlePrices::orderBy('updated_at', 'desc')
            ->get();

        return response()->json([
            'prices' => $prices,
            'message' => 'Prices loaded.',
        ], 200);
    }

    /**
     * Update Price
     *
     * @param Request $request
     * @return void
     */
    public function updatePrice(Request $request)
    {
        $data = $request->validate([
            'price_id' => ['required', 'numeric'],
            'is_active' => ['required', 'boolean'],
        ]);

        PaddlePrices::find($data['id'])?->update([
            'is_active' => (bool) $data['is_active']
        ]);

        return response()->json([
            'message' => 'Price updated.',
        ], 200);
    }

    /**
     * Find and load user-access
     *
     * @param string $email
     * @return void
     */
    public function loadUserAccess(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'string'],
        ]);

        if($user = User::where('email', $data['email'])->first()) {

            $userAccess = UserAccess::where('user_id', $user->id)
                ->orderBy('expiration_date', 'desc')
                ->get()
                ->map(function($access) {
                    return AccessCollection::renderUserAccess($access);
                });

            $userTransactions = PaddleTransactions::where('user_id', $user->id)
                ->orderBy('updated_at', 'desc')
                ->get()
                ->map(function($transaction) use($user) {
                    return AccessCollection::renderUserTransactions($transaction);
                });
            
            return response()->json([
                'access' => $userAccess,
                'transactions' => $userTransactions,
                'message' => 'User found.',
            ], 200);
        }

        return response()->json([
            'message' => 'No user found.',
        ], 422);
    }

    /**
     * Find and load user-access
     *
     * @param string $email
     * @return void
     */
    public function createUserAccess(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'string'],
            'access_token' => ['required', 'string'],
            'quantity' => ['nullable', 'numeric'],
            'expiration_date' => ['required', 'string'],
        ]);

        if($user = User::where('email', $data['email'])->first()) {
            $access = UserAccess::create([
                'user_id' => $user->id,
                'is_active' => true,
                'access_token' => $data['access_token'],
                'quantity' => $data['quantity'] ?? 0,
                'expiration_date' => $data['expiration_date'],
                'status' => 'completed',
                'message' => 'created.by.admin'
            ]);
            
            return response()->json([
                'access' => AccessCollection::renderUserAccess($access),
                'message' => 'Access created.',
            ], 200);
        }

        return response()->json([
            'message' => 'No user found.',
        ], 422);
    }

    /**
     * Find and load user-access
     *
     * @param string $email
     * @return void
     */
    public function updateUserAccess(Request $request)
    {
        $data = $request->validate([
            'access_id' => ['required', 'numeric'],
            'is_active' => ['required', 'boolean'],
        ]);

        UserAccess::find($data['access_id'])?->update([
            'is_active' => $data['is_active']
        ]);
        return response()->json([
            'message' => 'Access updated.',
        ], 200);
    }
}
