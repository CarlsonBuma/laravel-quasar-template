<?php

namespace App\Http\Controllers\Auth;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Collections\UserCollection;
use App\Http\Controllers\Access\AccessHandler;

class UserAuthController extends Controller
{
    /**
     * Default data for our Client-UI-Handling
     *
     * @return void
     */
    public function authUser()
    {
        $user = Auth::user();
        $userAccess = AccessHandler::getLatestUserAccesses($user->id);
        $userAccess = $userAccess->map(function($access) {
            return UserCollection::render_user_access($access);
        });

        return response()->json([
            'user' => UserCollection::render_user($user),
            'access' => $userAccess
        ], 200);
    }

    /**
     * User Login 
     *  > Attemps-Middleware: throttle:6,1
     *  > Check Token: $email_verified_at
     *  > Start Session
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function loginUser(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);

            // Check Credentials
            if (Auth::attempt([
                'email' => $credentials['email'],
                'password' => $credentials['password']
            ])) {
                
                // Check if Email is verified
                $user = User::find(Auth::id());
                if($user && !$user->email_verified_at instanceof Carbon) {
                    return response()->json([
                        'status' => 'email_not_verified',
                        'email' => $credentials['email'],
                        'message' => 'Please verify your email before accessing your account.',
                    ], 401);
                }

                // Create client-access token
                $token = $user->createToken('user')->accessToken;
                return response()->json([
                    'token' => $token,
                    'message' => 'Session started.'
                ], 200);
            }
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 401);
        }

        return response()->json([
            'message' => 'The provided credentials do not match our records.',
        ], 401);
    }

    /**
     * Remove session
     *
     * @return void
     */
    public function logoutUser()
    {
        // @intelephense-ignore next-line
        $user = Auth::user()->token()->delete();
        return response()->json([
            'message' => 'Session removed.'
        ], 200);
    }
}
