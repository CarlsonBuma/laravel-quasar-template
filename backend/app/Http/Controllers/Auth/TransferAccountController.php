<?php

namespace App\Http\Controllers\Auth;

use Exception;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Classes\Modulate;
use Illuminate\Support\Facades\DB;
use App\Mail\SendEmailVerification;
use App\Models\AccessSubscriptions;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;

class TransferAccountController extends Controller
{
    /**
     ** Transfer Account to new Emailadress
     **  > Before changing email, new user has to verify his new email adress
     **  > Old user is still able to undone its transfer, by verifying its old email again
     *
     * @param Request $request
     * @return void
     */
    public function sendToken(Request $request)
    {
        try {
            $data = $request->validate([
                'email' => ['required', 'string', 'email', 'unique:users', 'max:255'],  // Unique Email
                'password' => ['required', 'string', 'max:255'],
            ]);
            
            $user = (object) Auth::user();
            $newEmail = $data['email'];
            $password = $data['password'];
            
            // Validate
            if(!Hash::check($password, $user->password)) 
                throw new Exception('Ups, the given password is incorrect.');

            // Check if current Subscriptions existing
            // User must have canceled all its subscription
            // Before he can delete its account
            if(
                AccessSubscriptions::where([
                    'user_id' => Auth::id(),
                    'canceled_at' => null,
                ])->first()
            ) {
                return response()->json([
                    'message' => 'There are active subscriptions assigned to your account! 
                        Please cancel them first: "My avatar" > "My access".',
                ], 422);
            }
            
            // Process Transfer
            $userAccount = User::where('id', Auth::id())->first();
            DB::beginTransaction();

                // Start Transfer
                $token = Str::random(255);
                $userAccount->email_verified_at = null;
                $userAccount->token = $token;
                $userAccount->save();

                // Send verification Link
                $verificationLink = Modulate::signedLink('transfer.account', [
                    'email' => $user->email,
                    'token' => $token,
                    'transfer' => $newEmail
                ]);

                Mail::to($newEmail)->send(new SendEmailVerification($verificationLink, $user)); 

                // Remove Token
                $user->token()->delete();
            DB::commit(); 
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'Transfering user in progress...',
        ], 200);
    }

    /**
     ** Verify Email transfer
     **  > Validate URL & Token
     **  > New Email must be unique
     **  > Change email & update email_verified_at
     *
     * @param String $email
     * @param String $token
     * @param String $transfer (New Email)
     * @param Request $request
     * @return void
     */
    public function verifyToken(String $email, String $token, String $transfer, Request $request)
    {
        try {

            $data = $request->validate([
                'password' => ['required', 'string', 'max:255', 'confirmed', Password::defaults()],
                'terms' => ['required', 'boolean'],
            ]);

            // Validate
            $password = $data['password'];
            if(!$data['terms']) throw new Exception('Please agree with our Terms & Conditions.');
            if (!$request->hasValidSignature()) throw new Exception('Link has been expired.');
            if(User::where('email', $transfer)->first()) throw new Exception('Email alredy exists.');
            
            // Validate Token
            $user = User::where([
                'email' => $email,
                'email_verified_at' => null,
                'token' => $token
            ])->first();

            // Set User
            if(!$user) throw new Exception('Invalid verification key.');
            $user->email = $transfer;
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
            'message' => 'Your account has been transfered! Please login with your new email.',
        ], 200);
    }
}
