<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\AppAccess;


class AppAccessCockpit
{
    /**
     * As soon a user is making subscription trough our payment-gateway
     * We will receive an access-token, sent by Paddle. Users are able
     * navigating between additionals features accessible by this token.
     * 
     * Make sure to set "custom_data" in product-price in Paddle, to 
     * handle the issues access-token, with our accessToken 'access-cockpit'
     *  > https://vendors.paddle.com/
     * 
     * Users are paying for getting this access-token 'access-cockpit'
     *  *Logic see: App\Http\Controllers\User\ControllerPayments;
     *
     * @var string
     */
    static public $accessToken = 'access-cockpit';


    /**
     * Middleware
     * Access to features, according to user-issued access-token
     *
     * @param Request $request
     * @param Closure $next
     * @return void
     */
    public function handle(Request $request, Closure $next)
    {   
        if(Auth::id() && AppAccess::checkUserAccessByToken(Auth::id(), SELF::$accessToken)) 
            return $next($request);

        return response()->json([
            'access_token' => SELF::$accessToken,
            'status' => 'no_access_to_service',
            'message' => 'No access to current service.'
        ], 401);  
    }
}
