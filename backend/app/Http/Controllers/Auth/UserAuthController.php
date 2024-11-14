<?php

namespace App\Http\Controllers\Auth;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Collections\UserCollection;


class UserAuthController extends Controller
{
    /**
     * Authenticate user
     *
     * @return void
     */
    public function authUser()
    {
        $userID = User::find(Auth::id());
        return response()->json([
            'user' => UserCollection::render_user($userID),
            'access' => UserCollection::render_user_access($userID)
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

            // Check if Email is verified
            $user = User::where('email', $credentials['email'])->first();
            if($user && !$user->email_verified_at instanceof Carbon) {
                return response()->json([
                    'status' => 'email_not_verified',
                    'email' => $credentials['email'],
                    'message' => 'Please verify your email before accessing your account.',
                ], 401);
            }

            // Check Credentials
            if (Auth::attempt([
                'email' => $credentials['email'],
                'password' => $credentials['password']
            ])) {
                $token = User::find(Auth::id())->createToken('user')->accessToken;
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
     * Remove Session
     *
     * @return void
     */
    public function logoutUser()
    {
        try {
            $user = User::find(Auth::id());
            $user->token()->delete();
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
        return response()->json([
            'message' => 'Session removed.'
        ], 200);
    }
}
