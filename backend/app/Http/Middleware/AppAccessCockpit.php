<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\AppAccess\AppAccessHandler;

class AppAccessCockpit
{
    /**
     * Access to Feature "Cockpit", according to issued price access-token by paddle
     *  > Make sure set custom_data 'access_token' in product-price in Paddle
     *  > Current price access-token must be defined in .env file "APP_ACCESS_COCKPIT"
     *  > Watch-out: UI will handle Feature-Access-Token "Cockpit" as $Flag accordingly
     *  > https://vendors.paddle.com/
     *
     * @param Request $request
     * @param Closure $next
     * @return void
     */
    public function handle(Request $request, Closure $next)
    {   
        $accessToken = SELF::getAccessToken();
        if(AppAccessHandler::checkUserAccessByToken(Auth::id(), $accessToken)) 
            return $next($request);   

        return response()->json([
            'access_token' => $accessToken,
            'status' => 'no_access_to_service',
            'message' => 'No access to current service.'
        ], 401);  
    }

    /**
     * Get access token
     *
     * @return string
     */
    static public function getAccessToken(): string
    {
        return env('APP_ACCESS_COCKPIT');
    }
}
