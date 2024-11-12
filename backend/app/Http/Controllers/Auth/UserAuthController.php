<?php

namespace App\Http\Controllers\Auth;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Middleware\AccessUser;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\AccessBusinessCockpit;
use Illuminate\Support\Facades\Storage;

class UserAuthController extends Controller
{
    /**
     * Authenticate user
     *  > Get user credentials
     *  > Check user has access to backpanel
     *  > Check user subscriptions access
     *      > Access to Entity [$entityAccess]
     *      > ... comming soon
     *
     * @return void
     */
    public function authUser()
    {
        // User
        $user = User::find(Auth::id());
        $avatarPath = $user->avatar
            ? URL::to(Storage::url('userAvatar')) . '/' . $user->avatar
            : '';
        
        // Entities
        $userAvatar = $user->has_avatar()->first();
        $userEntity = $user->has_entity()->first();
        $isAdmin = $user->is_admin()->exists();
        $entityAccess = AccessUser::checkUserAccessByToken(Auth::id(), AccessBusinessCockpit::$accessToken);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'img_src' => $avatarPath,
                'email' => $user->email
            ],
            'avatar' => [
                'id' => $userAvatar->id,
                'is_community' => $userAvatar->is_community,
                'user_id' => $userAvatar->user_id,
            ],
            'entity' => [
                'id' => $userEntity->id,
                'is_community' => $userEntity->is_community,
                'user_id' => $userEntity->user_id,
            ],
            'access' => [
                'is_admin' => $isAdmin,
                'business_cockpit' => [
                    'access_token' => $entityAccess?->access_token,
                    'expiration_date' => $entityAccess?->expiration_date,
                ],
            ]
        ], 200);
    }

    /**
     ** User Verification 
     **  > Attemps-Middleware: throttle:6,1
     **  > Handle Verified Email
     **  > Start Session
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
                ], 403);
            }

            // Check Credentials
            if (Auth::attempt([
                'email' => $credentials['email'],
                'password' => $credentials['password']
            ])){
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
     ** Remove Session
     * @return void
     */
    public function logoutUser()
    {
        try {
            $user = (object) Auth::user();
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
