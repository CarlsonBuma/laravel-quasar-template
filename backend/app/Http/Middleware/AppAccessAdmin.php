<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Access\AccessHandler;

class AppAccessAdmin
{
    /**
     * Middleware to check user access for certain features within the application
     * 
     * **Definition:**
     * Validates user access based on the "access-admin" token
     * 
     * **Call:**
     * Triggered when a client tries to access certain features
     *  
     * **Action:**
     *  - Verifies the presence and validity of the "access-admin" token
     *  - Logic is implemented in the "\Controllers\Admin" folder
     *  
     * **Restrictions:**
     *  - The token must be issued to the user manually by admin
     * 
     * **Dependencies**
     *  - See: "\Controllers\Admin\BackpanelAccessController.php" for granting user access by admin
     *  - See: "\Controllers\Access\AccessHandler" for detailed token handling
     *
     * @param Request $request
     * @param Closure $next
     * @return void
     */
    public function handle(Request $request, Closure $next)
    {   
        $accessToken = AccessHandler::$tokenAdmin;
        if(AccessHandler::checkUserAccessByToken(Auth::id(), $accessToken)) 
            return $next($request);   

        return response()->json([
            'status' => 'no_admin',
            'message' => 'No access.',
        ], 401);  
    }
}
