<?php

namespace App\Http\Controllers\Auth;

use Exception;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\AccessSubscriptions;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class UserAccountController extends Controller
{
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
                Storage::disk('userAvatar')->delete($userImgSrc);
            } 
            
            // Change Image: Existing vs. non existing
            else if ($data['avatar']) {   
                $fileExtension = $request->file('avatar')->extension();
                $img_src = Auth::id() . '-' . Str::random(32) . '.' . $fileExtension;
                if($userImgSrc) Storage::disk('userAvatar')->delete($userImgSrc);       
                Storage::putFileAs('public/userAvatar', $request->file('avatar'), $img_src);
            }

            $user->avatar = $img_src;
            $user->save();
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => $data['delete']
                ? 'Your avatar has been deleted.'
                : 'Your avatar has been updated.',
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
            'message' => 'Success! Your username has been changed.',
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
            'message' => 'Success! Your password has been changed.',
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

            // Remove Files
            if($userImgSrc = $user->avatar) 
                Storage::disk('userAvatar')->delete($userImgSrc);
            if($entityImgSrc = $user->has_entity()->first()?->avatar) 
                Storage::disk('entityAvatar')->delete($entityImgSrc);

            // Delete user
            $user->delete();
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'Your account has been removed.',
        ], 200);
    }
}
