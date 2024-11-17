<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class EmailVerified
{
    /**
     * User email must be verified.
     *
     * @param Request $request
     * @param Closure $next
     * @return void
     */
    public function handle(Request $request, Closure $next)
    {  
        // E-Mail must be verified
        $user = Auth::user(); 
        if($user->email_verified_at)
            return $next($request);

        // Remove user-access, in case email has been 'unverified' by actions
        // May caused by new user's 'email-transactions-verification'
        else if($user && !$user->email_verified_at) {
            // @intelephense-ignore next-line
            $user->token()->delete();
        }

        // Email not verified
        return response()->json([
            'status' => 'email_not_verified',
            'email' => $user->email,
            'message' => 'Please verify email.'
        ], 401);
    }
}
