<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Access\AccessHandler;

class AppAccessCockpit
{
    /**
     ** Middleware to check user access for certain features within the application.
     * 
     * **Definition:**
     * Validates user access based on the "access-cockpit" token
     * 
     * **Call:**
     * Triggered when a client tries to access certain features
     *  
     * **Action:**
     *  - Verifies the presence and validity of the "business-admin" token
     *  - Logic is implemented in the "\Controllers\Admin" folder.
     *  
     * **Restrictions:**
     *  - The token must be issued to user by admin or by our webhook
     * 
     * **Dependencies**
     *  - See: "\Controllers\Admin\BackpanelAccessController.php" for granting user access by admin
     *  - See: "\Listeners\PaddleWebhookListener.php" for detailed access handling
     *  - See: "\Controllers\Access\AccessHandler" for detailed token handling
     *
     * @param Request $request
     * @param Closure $next
     * @return void
     */
    public function handle(Request $request, Closure $next)
    {   
        $accessToken = AccessHandler::$tokenCockpit;
        if(AccessHandler::checkUserAccessByToken(Auth::id(), $accessToken)) 
            return $next($request);   

        return response()->json([
            'access_token' => $accessToken,
            'status' => 'no_access_to_feature',
            'message' => 'No access to feature.'
        ], 401);  
    }
}
