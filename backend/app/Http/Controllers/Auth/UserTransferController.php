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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;

class UserTransferController extends Controller
{
    /**
     * Transfer Account to new Emailadress
     *  > Before changing email, new user has to verify his new email adress
     *  > Old user is still able to undone its transfer, by verifying its old email again
     *      > See "/EmailVerificationController"
     *  > Check if active Subscriptions are assigend to account (Middleware)
     *
     * @param Request $request
     * @return void
     */
    public function initializeEmailTransfer(Request $request)
    {
        try {
            $data = $request->validate([
                'email' => ['required', 'string', 'email', 'unique:users', 'max:255'],  // Unique Email
                'password' => ['required', 'string', 'max:255'],
            ]);
            
            // Validate
            $user = User::find(Auth::id());
            if(!Hash::check($data['password'], $user->password)) 
                throw new Exception('Ups, the given password is incorrect.');
            
            // Process Transfer
            DB::beginTransaction();

                // Start Transfer
                $token = Str::random(255);
                $user->email_verified_at = null;
                $user->token = $token;
                $user->save();

                // Send verification Link
                $verificationLink = Modulate::signedLink('transfer.account', [
                    'email' => $user->email,
                    'token' => $token,
                    'transfer' => $data['email']
                ]);
                
                Mail::to($data['email'])->send(new SendEmailVerification($verificationLink, $user)); 
                
                // Logout
                // @intelephense-ignore next-line
                Auth::user()->token()->delete();
            DB::commit(); 
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'Token sent to provided email.',
        ], 200);
    }

    /**
     * Verify Email transfer
     *  > Validate URL & Token
     *  > New Email must be unique
     *  > Change email & update email_verified_at
     *
     * @param String $email
     * @param String $token
     * @param String $transfer (New Email)
     * @param Request $request
     * @return void
     */
    public function verifyEmailTransfer(String $email, String $token, String $transfer, Request $request)
    {
        try {
            $data = $request->validate([
                'password' => ['required', 'string', 'max:255', 'confirmed', Password::defaults()],
                'terms' => ['required', 'boolean'],
                'privacy' => ['required', 'boolean'],
            ]);

            // Legal
            if(!$data['terms'] || !$data['privacy']) 
                throw new Exception('Please accept our terms-of-use.');
            
            // Validate Token
            if (!$request->hasValidSignature()) 
                throw new Exception('Link has been expired.');
            
            $user = User::where([
                'email' => $email,
                'email_verified_at' => null,
                'token' => $token
            ])->first();

            // Validate new email
            if(!$user) throw new Exception('Invalid verification key.');
            if(User::where('email', $transfer)->exists()) {
                $user->email_verified_at = now();
                $user->save();
                throw new Exception('This email is already in use. Please log in with your previous credentials.');
            }
            
            // Set new email
            $user->email = $transfer;
            $user->password = Hash::make($data['password']);
            $user->email_verified_at = now();
            $user->token = null;
            $user->save();
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'token' => $user->createToken('user')->accessToken,
            'message' => 'New email verified.',
        ], 200);
    }
}
