<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsAdmin
{
    public function handle(Request $request, Closure $next)
    {   
        if(Auth::user() && Auth::user()->is_admin->user_id) 
                return $next($request); 
            
        return response()->json([
            'status' => 'no_admin',
            'message' => 'No admin access.',
        ], 403); 
    }
}
