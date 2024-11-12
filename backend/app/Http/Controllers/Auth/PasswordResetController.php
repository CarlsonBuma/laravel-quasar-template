<?php

namespace App\Http\Controllers\Auth;

use Exception;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Classes\Modulate;
use App\Mail\SendPasswordReset;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

class PasswordResetController extends Controller
{
    /**
     ** Send Reset Email
     **  > Generate URL, with Token 
     **  > Send verification link to new email
     *
     * @param Request $request
     * @return void
     */
    public function sendToken(Request $request)
    {
        try {
            $data = $request->validate([
                'email' => ['required', 'email'],
            ]);

            // Create Reset Token
            $user = User::where('email', $data['email'])->first();
            if ($user) {
                DB::beginTransaction();
                    $token = Str::random(255);
                    $user->token = $token;
                    $user->save();
                    
                    // Send verification Link
                    $verificationLink = Modulate::signedLink('password.reset', [
                        'email' => $user->email,
                        'token' => $token
                    ]);

                    Mail::to($user)->send(new SendPasswordReset($verificationLink, $user));
                DB::commit();
            }
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'The token has been sent to your email.',
        ], 200); 
    }

    /**
     ** Create new User Passwort
     **  > Check Password Requirements
     **  > Validate URL & Token
     **  > Update current Password (hashed)
     *
     * @param Request $request
     * @return void
     */
    public function verifyToken(String $email, String $token, Request $request) 
    {
        try {
            $data = $request->validate([
                'password' => ['required', 'string', 'max:255', 'confirmed', Password::defaults()],
            ]);

            // Validate Signature
            $password = $data['password'];
            if (!$request->hasValidSignature()) throw new Exception('Link has been expired.');

            // Check Token
            $user = User::where([
                'email' => $email,
                'token' => $token
            ])->first();

            // Set User
            if(!$user) throw new Exception('Invalid verification key.');
            $user->password = Hash::make($password);
            $user->token = null;
            $user->save();
        } catch(Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }

        // Login
        $token = $user->createToken('user')->accessToken;
        return response()->json([
            'token' => $token,
            'message' => 'Your password has been updated.'
        ], 200);
    }
}
