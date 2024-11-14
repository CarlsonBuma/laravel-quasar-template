<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use App\Models\UserAvatar;
use Illuminate\Http\Request;
use App\Models\AppGeolocations;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Collections\UserCollection;

class UserAvatarController extends Controller
{
    /**
     * Load user avatar
     *
     * @return void
     */
    public function loadAvatar() 
    {
        return response()->json([
            'avatar' => UserCollection::render_user_avatar(User::find(Auth::id())),
            'message' => 'User avatar loaded.',
        ], 200);
    }

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

        UserAvatar::where('user_id', Auth::id())->update([
            'is_public' => (bool) $data['is_public'],
        ]);

        return response()->json([
            'message' => (bool) $data['is_public'] 
                ? 'Your avatar is now public available.' 
                : 'Your avatar is set to private.'
        ], 200);
    }

    /**
     * Update about
     *
     * @param Request $request
     * @return void
     */
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

    /**
     * Set Location
     *
     * @param Request $request
     * @return void
     */
    public function updateLocation(Request $request) 
    {
        $data = $request->validate([
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

        $geolocation = new AppGeolocations();
        UserAvatar::where('user_id', Auth::id())->update([
            'location_id' => $geolocation->add_new_entry($data),
        ]);

        return response()->json([
            'message' => 'Location has been updated.',
        ], 200);
    }

    /**
     * Update country
     *
     * @param Request $request
     * @return void
     */
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
}
