<?php

namespace App\Http\Collections;

use Illuminate\Support\Facades\URL;
use App\Http\Middleware\AccessCommunity;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Collection;

abstract class AvatarCollection
{
    public static $avatar = [
        'avatar_id' => 0,
        'user' => [],
        'is_community' => false,
        'is_available' => false,
        'date_of_availability' => '',
        'contact' => '',
        'contact_is_public' => false,
        'age' => 0,
        'age_is_public' => false,
        'location' => null,
        'location_is_public' => false,
        'about' => '',
        'language' =>  [],
    ];

    /**
     * User Avatar
     * Only Accessible by current User
     *
     * @param object|null $user
     * @param object|null $userAvatar
     * @return array
     */
    static public function render_user_avatar(object|null $user, object|null $userAvatar): array
    {
        if(!$user) return SELF::$avatar;
        else if(!$userAvatar) return SELF::$avatar;
            
        // Render Avatar Profile
        $language = SELF::render_user_language_pivot($user->has_language_pivot);
        $geoLocation = $userAvatar->location_id
            ? GeolocationCollection::render_geoLoaction($userAvatar->belongs_to_location, $showAddress = true)
            : GeolocationCollection::$geoLocation;
        $country = $userAvatar->country_id
            ? [
                'id' => $userAvatar->country_id,
                'name' => $userAvatar->belongs_to_country->name,
                'code' => $userAvatar->belongs_to_country->code,
            ] : null;
        
        // Parse Avatarprofile
        return [
            'avatar_id' => $userAvatar->id,
            'is_community' => $userAvatar->is_community,
            'is_available' => $userAvatar->is_available,
            'date_of_availability' => $userAvatar->date_of_availability,
            'contact' => $userAvatar->contact,
            'contact_is_public' => $userAvatar->contact_is_public,
            'age' => (int) $userAvatar->age,
            'age_is_public' => $userAvatar->age_is_public,
            'country' => $country,
            'location' => $geoLocation,
            'location_is_public' => $userAvatar->location_is_public,
            'about' => $userAvatar->about,
            'language' => $language
        ];
    }

    /**
     * Render Public Community Avatar
     *  > Check if Avatar is accessible
     *
     * @param object|null $avatar
     * @return array
     */
    static public function render_community_avatar(object $avatar = null): array
    {
        if(!$avatar || !AccessCommunity::avatarIsAccessible($avatar)) return SELF::$avatar;
        $user = $avatar->belongs_to_user;
        $userProfile = SELF::render_user_profile($user);
        $language = SELF::render_user_language_pivot($user->has_language_pivot);
        $contact = $avatar->contact_is_public
            ? $avatar->contact
            : '';
        $geoLocation = $avatar->location_is_public && $avatar->location_id
            ? GeolocationCollection::render_geoLoaction($avatar->belongs_to_location, $showAddress = false)
            : GeolocationCollection::$geoLocation;
        $avatarAge = $avatar->age_is_public
            ? $avatar->age
            : 0;
        $country = $avatar->country_id
            ? [
                'id' => $avatar->country_id,
                'name' => $avatar->belongs_to_country->name,
                'code' => $avatar->belongs_to_country->code,
            ] : null;

        return [
            'user_profile' => $userProfile,
            'avatar_id' => $avatar->id,
            'is_community' => $avatar->is_community,
            'contact' => $contact,
            'age' => $avatarAge,
            'location' => $geoLocation,
            'about' => $avatar->about,
            'country' => $country,
            'language' => $language
        ];
    }

    /**
     * Render User Profile
     *
     * @param object|null $user
     * @return array
     */
    static public function render_user_profile(object $user = null): array
    {
        $avatarPath = $user?->avatar
            ? URL::to(Storage::url('userAvatar')) . '/' . $user->avatar
            : '';  
        
        return [
            'id' => $user?->id,
            'name' => $user?->name,
            'avatar' => $avatarPath,
        ];
    }

    /**
     * Render User Languages
     *
     * @param Collection $PivotUserLanguages
     * @return array
     */
    public static function render_user_language_pivot(Collection $PivotUserLanguages): array 
    {
        $language = [];
        foreach($PivotUserLanguages as $pivot) {
            array_push($language, $pivot->belongs_to_language);
        }
        return $language;
    }
}