<?php

namespace App\Http\Controllers\UserEntity;

use Exception;
use App\Models\UserEntity;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Classes\Modulate;
use App\Models\AppGeolocations;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Collections\EntityCollection;


class EntityProfileController extends Controller
{
    /**
     * Load entity profile
     *
     * @return void
     */
    public function loadProfile() 
    {
        $renderedEntity = EntityCollection::render_user_entity(
            UserEntity::where('user_id', Auth::id())->first(),
        );

        return response()->json([
            'entity' => $renderedEntity,
            'message' => 'User entity loaded.',
        ], 200);
    }

    /**
     * Update publicity
     *  > Flag: $is_public
     *
     * @param Request $request
     * @return void
     */
    public function updatePublicity(Request $request)
    {
        $data = $request->validate([
            'is_public' => ['required', 'boolean'],
        ]);
        
        UserEntity::where('user_id', Auth::id())->update([
            'is_public' => (bool) $data['is_public'],
        ]);

        return response()->json([
            'message' => (bool) $data['is_public'] 
                ? 'Entity published.' 
                : 'Entity set to private.'
        ], 200);
    }
    
    /**
     * Update avatar image
     *
     * @param Request $request
     * @return void
     */
    public function updateAvatar(Request $request) 
    {
        $data = $request->validate([
            'src' => ['nullable', 'mimes:jpg,jpeg,png', 'max:2048'],     // Update Banner if set
            'avatar_delete' => ['required', 'boolean'],                  // Check if user wants to delete Avatar
        ]);

        // Process Avatar
        $userEntity = UserEntity::where('user_id', Auth::id())->first();
        if(!$userEntity) throw new Exception('Entity does not exist.');
        $currentAvatarImageLink = $userEntity->avatar;
        if($data['avatar_delete']) {
            if($currentAvatarImageLink) Storage::disk('entity')->delete($currentAvatarImageLink);
            $currentAvatarImageLink = null;
        } else if(isset($data['src'])) {
            if($currentAvatarImageLink) Storage::disk('entity')->delete($currentAvatarImageLink);  
            $fileExtension = $request->file('src')->extension();
            $currentAvatarImageLink = Auth::id() . '-' . Str::random(45) . '.' . $fileExtension;
            Storage::putFileAs('public/entity', $request->file('src'), $currentAvatarImageLink);
        }

        // Add Link to image
        $userEntity->update([
            'avatar' => $currentAvatarImageLink,
        ]);

        return response()->json([
            'message' => 'Avatar updated.',
        ], 200);
    }

    /**
     * Update Credentials
     *
     * @param Request $request
     * @return void
     */
    public function updateName(Request $request) 
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        UserEntity::where('user_id', Auth::id())->update([
            'name' => $data['name'],
        ]);

        return response()->json([
            'message' => 'Name updated.',
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
            'about' => ['nullable', 'string', 'max:1999'],
        ]);

        UserEntity::where('user_id', Auth::id())->update([
            'about' => $data['about'],
        ]);

        return response()->json([
            'message' => 'About has been updated.',
        ], 200);
    }

    /**
     * Update impressumg
     *
     * @param Request $request
     * @return void
     */
    public function updateImpressum(Request $request) 
    {
        $data = $request->validate([
            'website' => ['nullable', 'string', 'max:255'],
            'contact' => ['nullable', 'string', 'max:999'],
        ]);

        $websiteSanitized = Modulate::sanitizeLink($data['website']);
        UserEntity::where('user_id', Auth::id())->update([
            'website' => $websiteSanitized,
            'contact' => $data['contact'],
        ]);

        if($data['website'] && !$websiteSanitized) {
            return response()->json([
                'message' => 'Invalid link.',
            ], 422);
        }

        return response()->json([
            'message' => 'Impressum updated.',
        ], 200);
    }

    /**
     * Update tags
     *
     * @param Request $request
     * @return void
     */
    public function updateTags(Request $request) 
    {
        $data = $request->validate([
            'tags' => ['nullable', 'array'],
        ]);

        UserEntity::where('user_id', Auth::id())->update([
            'tags' => $data['tags'],
        ]);

        return response()->json([
            'message' => 'Bulletpoints updated.',
        ], 200);
    }

    /**
     * Update Geolocation
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
        UserEntity::where('user_id', Auth::id())->update([
            'location_id' => $geolocation->add_new_entry($data),
        ]);

        return response()->json([
            'message' => 'Location updated.',
        ], 200);
    }
}
