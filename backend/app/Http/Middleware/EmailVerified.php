<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailVerified
{
    public function handle(Request $request, Closure $next)
    {  
        $user = (object) Auth::user(); 
        if($user->email_verified_at)
            return $next($request);
        
        else if($user && !$user->email_verified_at)
            $user->token()->delete();

        // Email not verified
        return response()->json([
            'status' => 'email_not_verified',
            'email' => $user->email,
            'message' => 'Please verify your email before accessing your account.'
        ], 403);
    }
}
