<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\PaddleSubscriptions;
use Illuminate\Support\Facades\Auth;


class PaddleNoActiveSubscriptions
{
    /**
     * Check active user subscriptions
     *
     * @param Request $request
     * @param Closure $next
     * @return void
     */
    public function handle(Request $request, Closure $next)
    {  
        if(
            !PaddleSubscriptions::where([
                'user_id' => Auth::id(),
                'canceled_at' => null,
            ])->exists()
        ) return $next($request); 

        return response()->json([
            'status' => 'active_subscriptions',
            'message' => 'Please cancel active subscriptions.',
        ], 422);
    }
}
