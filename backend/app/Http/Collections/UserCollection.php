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
     * @param object $user
     * @return array
     */
    static public function render_user(object $user): array
    {
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
     * @param object $user
     * @return array
     */
    static public function render_user_access(object $user): array
    {
        $entityAccess = AppAccessHandler::checkUserAccessByToken($user->id, AppAccessCockpit::getAccessToken());
        return [
            '_type' => 'Collection $access',
            'is_admin' => $user->is_admin->exists(),
            'access_cockpit' => [
                'access_token' => $entityAccess?->access_token,
                'expiration_date' => $entityAccess?->expiration_date,
            ],
        ];
    }
}