<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\UserEntity;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use App\Models\AccessUsers;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Middleware\AccessUser;

class AccessBusinessCockpit
{
    /**
     * As soon a user is making subscription trough our payment-gateway
     * We will receive an access-token, sent by Paddle. Users are able
     * navigating between additionals features accessible by this token.
     * 
     * Make sure to set "custom_data" in product-price in Paddle, to 
     * handle the issues access-token, with our accessToken 'business-cockpit'
     *  > https://vendors.paddle.com/
     * 
     * Users are paying for getting this access-token 'business-cockpit'
     *  *Logic see: App\Http\Controllers\User\ControllerPayments;
     *
     * @var string
     */
    static public $accessToken = 'business-cockpit';


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
        if(Auth::id() && AccessUser::checkUserAccessByToken(Auth::id(), SELF::$accessToken)) 
            return $next($request);

        return response()->json([
            'access_token' => SELF::$accessToken,
            'status' => 'no_access_to_service',
            'message' => 'No access to current service.'
        ], 403);  
    }

    /**
     * Get accessible entities query
     *
     * @param string $accessToken
     * @return Builder
     */
    static public function getAccessibleEntities(): Builder
    {
        $accessbibleEntities = AccessUsers::join('user_entity', 'access_users.entity_id', '=', 'user_entity.id')
            ->whereNotNull('access_users.entity_id')
            ->where('access_users.access_token', SELF::$accessToken)
            ->where('user_entity.is_community', true)
            ->where('user_entity.name', '!=', null)
            ->where('user_entity.avatar', '!=', null)
            ->where('access_users.is_active', true)
            ->whereDate('access_users.expiration_date', '>=', now())
            ->select('user_entity.id')
            ->distinct()
            ->pluck('user_entity.id');

        return UserEntity::whereIn('user_entity.id', $accessbibleEntities);
    }
}
