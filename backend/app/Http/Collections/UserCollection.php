<?php

namespace App\Http\Collections;

use App\Http\Controllers\Auth\AppAccess\AppAccessHandler;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;
use App\Http\Middleware\AppAccessCockpit;

abstract class UserCollection
{
    /**
     * Render user and its access
     *
     * @param object|null $user
     * @return array
     */
    static public function render_user(object $user = null): array
    {
        if(!$user) return [];
        return [
            '_type' => 'Collection $user',
            'id' => $user->id,
            'name' => $user->name,
            'avatar' => $user->avatar
                ? URL::to(Storage::url('user')) . '/' . $user->avatar
                : '',
            'email' => $user->email,
            'is_public' => $user->is_public
        ];
    }

    /**
     * Render user and its access-tokens
     *  > According to Logic
     *
     * @param object|null $user
     * @return array
     */
    static public function render_user_access(object $user = null): array
    {
        if(!$user) return [];

        $entityAccess = AppAccessHandler::checkUserAccessByToken(
            $user->id, 
            AppAccessCockpit::getAccessToken()
        );
        
        return [
            '_type' => 'Collection $access',
            'is_admin' => $user->is_admin->exists(),
            'access_cockpit' => [
                'access_token' => $entityAccess?->access_token,
                'expiration_date' => $entityAccess?->expiration_date,
            ],
        ];
    }

    /**
     * User Avatar
     * Only Accessible by current User
     *
     * @param object|null $user
     * @param object|null $userAvatar
     * @return array
     */
    static public function render_user_avatar(object|null $user): array
    {
        if(!$user) return [];
        
        $userAvatar = $user->has_avatar->first();
        if(!$userAvatar) return [];
            
        $geoLocation = $userAvatar->location_id
            ? GeolocationCollection::render_geoLoaction($userAvatar->belongs_to_location, $showAddress = true)
            : GeolocationCollection::$geoLocation;
        $country = $userAvatar->country_id
            ? [
                'id' => $userAvatar->country_id,
                'name' => $userAvatar->belongs_to_country->name,
                'code' => $userAvatar->belongs_to_country->code,
            ] : null;
        
        return [
            '_type' => 'Collection $userAvatar',
            'user' => SELF::render_user($user),
            'id' => $userAvatar->id,
            'about' => $userAvatar->about,
            'contact' => $userAvatar->contact,
            'location' => $geoLocation,
            'country' => $country,
            'is_public' => $userAvatar->is_public    // Flag
        ];
    }
}