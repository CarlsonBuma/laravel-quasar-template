<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\UserAvatar;
use App\Models\AppLanguages;
use Illuminate\Http\Request;
use App\Models\AppGeolocations;
use App\Models\PivotUserLanguages;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Collections\AvatarCollection;

class UserProfileController extends Controller
{
    //* Load Users Avatar Profile
    public function loadProfile() 
    {
        $userID = Auth::id();
        $user = User::find($userID);
        $userAvatar = $user->has_avatar()->first();
        $renderedAvatar = AvatarCollection::render_user_avatar($user, $userAvatar);
        
        return response()->json([
            'avatar' => $renderedAvatar,
            'message' => 'User avatar loaded.',
        ], 200);
    }

    //* Update, if Avatar is public accessible
    public function updatePublicity(Request $request)
    {
        $data = $request->validate([
            'is_public' => ['required', 'boolean'],
        ]);

        UserAvatar::where('user_id', Auth::id())->update([
            'is_community' => (bool) $data['is_public'],
        ]);

        $message = (bool) $data['is_public'] 
            ? 'Your avatar is now public available.' 
            : 'Your avatar is set to private.';

        return response()->json([
            'message' => $message
        ], 200);
    }
    
    /*****************************
     ** Avatar Management
     *****************************/
    //* Contact
    public function updateContact(Request $request) 
    {
        $data = $request->validate([
            'contact' => ['nullable', 'string'],
            'is_public' => ['required', 'boolean'],
        ]);

        UserAvatar::where('user_id', Auth::id())->update([
            'contact' => $data['contact'],
            'contact_is_public' => (bool) $data['is_public'],
        ]);

        return response()->json([
            'message' => 'Contact has been updated.',
        ], 200);
    }

    //* Date of Birth
    public function updateBirth(Request $request) 
    {
        $data = $request->validate([
            'age' => ['nullable', 'numeric'],
            'is_public' => ['required', 'boolean'],
        ]);

        UserAvatar::where('user_id', Auth::id())->update([
            'age' => $data['age'],
            'age_is_public' => (bool) $data['is_public'],
        ]);

        return response()->json([
            'message' => 'Age has been updated.',
        ], 200);
    }

    //* Google Location API
    public function updateLocation(Request $request) 
    {
        $locationID = null;
        $data = $request->validate([
            'is_public' => ['required', 'boolean'],
            'place_id' => ['nullable', 'string', 'max:255'],
            'lng' => ['nullable', 'numeric'],
            'lat' => ['nullable', 'numeric'],
            'address' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:99'],
            'country_short' => ['nullable', 'string', 'max:99'],
            'area' => ['nullable', 'string', 'max:99'],
            'area_short' => ['nullable', 'string', 'max:99'],
            'zip_code' => ['nullable', 'string', 'max:19'],
        ]);

        if(isset($data['place_id'])) {
            $locationID = AppGeolocations::firstOrCreate([
                'place_id' => $data['place_id'],
                'lng' =>  $data['lng'],
                'lat' =>  $data['lat'],
                'address' =>  $data['address'],
                'country' =>  $data['country']
            ], [
                'country_short' =>  $data['country_short'],
                'area' =>  $data['area'],
                'area_short' =>  $data['area_short'],
                'zip_code' =>  $data['zip_code']
            ])->id;
        }

        UserAvatar::where('user_id', Auth::id())->update([
            'location_id' => $locationID,
            'location_is_public' => (bool) $data['is_public'],
        ]);

        return response()->json([
            'message' => 'Location has been updated.',
        ], 200);
    }

    //* About
    public function updateAbout(Request $request) 
    {
        $data = $request->validate([
            'about' => ['nullable', 'string', 'max:499'],
        ]);

        UserAvatar::where('user_id', Auth::id())->update([
            'about' => $data['about'],
        ]);

        return response()->json([
            'message' => 'About has been updated.',
        ], 200);
    }

    //* Country
    public function updateCountry(Request $request) 
    {
        $data = $request->validate([
            'country_id' => ['nullable', 'numeric'],
        ]);

        UserAvatar::where('user_id', Auth::id())->update([
            'country_id' => $data['country_id'],
        ]);

        return response()->json([
            'message' => 'Resident has been updated.',
        ], 200);
    }

    //* Languages
    public function updateLanguages(Request $request) 
    {
        $data = $request->validate([
            'languages' => ['nullable', 'array'],
        ]);

        $userID = Auth::id();
        PivotUserLanguages::where('user_id', $userID)->delete();
        if($data['languages']) {
            foreach($data['languages'] as $language) {
                if(AppLanguages::where('id', (int) $language['id'])->exists()) {
                    PivotUserLanguages::create([
                        'user_id' => $userID,
                        'language_id' => (int) $language['id']
                    ]);
                }
            }
        }

        return response()->json([
            'message' => 'Language has been updated.',
        ], 200);
    }
}
