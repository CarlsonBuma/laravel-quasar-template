<?php

namespace App\Http\Controllers\Auth;

use Exception;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\PaddleSubscriptions;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class UserAccountController extends Controller
{
    /**
     * Allow public access
     *  > Flag: $public_access
     *
     * @param Request $request
     * @return void
     */
    public function updatePublicity(Request $request)
    {
        $data = $request->validate([
            'is_public' => ['required', 'boolean'],
        ]);

        User::find(Auth::id())->update([
            'is_public' => (bool) $data['is_public'],
        ]);

        return response()->json([
            'message' => (bool) $data['is_public'] 
                ? 'Your avatar is public.' 
                : 'Your avatar is private.'
        ], 200);
    }

    /**
     ** Change Avatar
     **  > Update Avatar
     **      > Delete Old Avatar
     **      > Add new Avatar
     **      > Link new Avatar with DB
     **  > Delete Avatar
     **      > Delete Old Avatar 
     *
     * @param Request $request
     * @return void
     */
    public function changeAvatar(Request $request) {
        try {

            $data = $request->validate([
                'avatar' => ['nullable', 'mimes:jpg,jpeg,png', 'max:2048'],
                'avatar_delete' => ['required', 'boolean'],
            ]);

            // User's Avatar
            $img_src = null;
            $user = User::find(Auth::id());
            $userImgSrc = $user->avatar;
            
            // Delete Avatar
            if($data['avatar_delete'] && $user->avatar) {
                Storage::disk('user')->delete($userImgSrc);
            } 
            
            // Change Image: Existing vs. non existing
            else if ($data['avatar']) {   
                $fileExtension = $request->file('avatar')->extension();
                $img_src = Auth::id() . '-' . Str::random(32) . '.' . $fileExtension;
                if($userImgSrc) Storage::disk('user')->delete($userImgSrc);       
                Storage::putFileAs('public/user', $request->file('avatar'), $img_src);
            }

            $user->avatar = $img_src;
            $user->save();
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => $data['avatar_delete']
                ? 'Avatar deleted.'
                : 'Avatar updated.',
        ], 200);
    }

    /**
     ** Change Username
     *
     * @param Request $request
     * @return void
     */
    public function changeName(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        User::find(Auth::id())->update([
            'name' => $data['name'],
        ]);

        return response()->json([
            'message' => 'Username updated.',
        ], 200);
    }

    /**
     ** Update Password
     **  > Check Password requirements
     **  > Confirm old Password
     *
     * @param Request $request
     * @return void
     */
    public function changePassword(Request $request)
    {
        try {
            $data = $request->validate([
                'password_current' => ['required', 'string', 'max:255'],
                'password' => ['required', 'string', 'max:255', 'confirmed', Password::defaults()],
            ]);
            
            // Check Current Password
            $user = User::find(Auth::id());
            if(!Hash::check($data['password_current'], $user->password)) 
                throw new Exception('Ups, the given password is incorrect.');
            
            // Update Password
            $user->update([
                'password' => Hash::make($data['password'])
            ]);

        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'Password updated.',
        ], 200);
    }

    /**
     ** Delete User Account
     **  > Remove Avatar
     **  > Logout User
     *
     * @param Request $request
     * @return void
     */
    public function deleteAccount(Request $request)
    {
        try {
            $data = $request->validate([
                'password' => ['required', 'string', 'max:255'],
            ]);
    
            $user = User::find(Auth::id());
            if(!Hash::check($data['password'], $user->password)) 
                throw new Exception('The given password is incorrect.');

            // Check if current Subscriptions existing
            // User must have canceled all its subscription
            // Before he can delete its account
            if(
                PaddleSubscriptions::where([
                    'user_id' => Auth::id(),
                    'canceled_at' => null,
                ])->first()
            ) {
                return response()->json([
                    'message' => 'There are active subscriptions assigned to your account! 
                        Please cancel them first: "My avatar" > "My access".',
                ], 422);
            }

            // Remove Files
            if($userImgSrc = $user->avatar) 
                Storage::disk('user')->delete($userImgSrc);
            if($entityImgSrc = $user->has_entity()->first()?->avatar) 
                Storage::disk('entity')->delete($entityImgSrc);

            // Delete user
            $user->delete();
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'Account removed.',
        ], 200);
    }
}
