<?php

namespace App\Http\Controllers\Auth;

use Exception;
use App\Models\User;
use App\Models\UserEntity;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class CreateAccountController extends Controller
{
    /**
     * Registration / Create Account
     *  > Creates new User
     *  > Create user entity
     *
     * @param Request $request
     * @return void
     */
    public function register(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => ['required', 'string', 'max:125'],
                'email' => ['required', 'string', 'email', 'unique:users', 'max:255'],
                'terms' => ['required', 'boolean'],
                'privacy' => ['required', 'boolean'],
            ]);
    
            // Legal
            if(!$data['terms'] || !$data['privacy']) 
                throw new Exception('Please accept our terms-of-use.');

            // Process
            $userID = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' =>  Hash::make(Str::random(125))
            ])->id;

            UserEntity::create([
                'user_id' => $userID,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
        
        return response()->json([
            'message' => 'Success! Your account has been created.',
        ], 200);
    }
}
