<?php

namespace App\Http\Controllers\Auth;

use Exception;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Classes\Modulate;
use Illuminate\Support\Facades\DB;
use App\Mail\SendEmailVerification;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;


class EmailVerificationController extends Controller
{
    /**
     * Request email verificaton
     * > Generate URL, with Token 
     * > Send verification link
     *
     * @param Request $request
     * @return void
     */
    public function sendToken(Request $request)
    {
        try {
            $data = $request->validate([
                'email' => ['required', 'string', 'email', 'max:255'],
            ]);

            $user = User::where('email', $data['email'])->first();
            if ($user && !$user->email_verified_at) {
                DB::beginTransaction();
                    
                    // Save token
                    $token = Str::random(255);
                    $user->token = $token;
                    $user->save();

                    // Create Link
                    $verificationToken = Modulate::signedLink('email.verification', [
                        'email' => $user->email,
                        'token' => $token
                    ]);

                    // Send email
                    Mail::to($user)->send(new SendEmailVerification($verificationToken, $user));
                DB::commit(); 
            }
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'Token has been sent to your email.',
        ], 200);
    }

    /**
     * Verify Email
     *  > Validate URL & Token
     *  > Check if transfer-email is unique
     *  > Change email & update email_verified_at
     *
     * @param String $id
     * @param String $hash
     * @param Request $request
     * @return void
     */
    public function verifyToken(String $email, String $token, Request $request)
    {      
        try {
            $data = $request->validate([
                'password' => ['required', 'string', 'max:255', 'confirmed', Password::defaults()],
            ]);

            // Check link signature
            $password = $data['password'];
            if (!$request->hasValidSignature()) throw new Exception('Link has been expired.');

            // Validate Token
            $user = User::where([
                'email' => $email,
                'email_verified_at' => null,
                'token' => $token
            ])->first();
            
            // Validate User
            if(!$user) throw new Exception('Invalid verification key.');
            
            $user->password = Hash::make($password);
            $user->email_verified_at = now();
            $user->token = null;
            $user->save();
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        // Login
        $token = $user->createToken('user')->accessToken;
        return response()->json([
            'token' => $token,
            'message' => 'Welcome! Your email has been verified.'
        ], 200);
    }
}
