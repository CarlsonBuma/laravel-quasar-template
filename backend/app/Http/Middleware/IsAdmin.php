<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsAdmin
{
    /**
     * User must exist as admin
     *  > Flag: $user_id,
     *  > Flag: ? $role
     *
     * @param Request $request
     * @param Closure $next
     * @return void
     */
    public function handle(Request $request, Closure $next)
    {   
        if(Admin::where('user_id', Auth::id())->exists()) 
                return $next($request); 
            
        return response()->json([
            'status' => 'no_admin',
            'message' => 'No admin access.',
        ], 401); 
    }
}
