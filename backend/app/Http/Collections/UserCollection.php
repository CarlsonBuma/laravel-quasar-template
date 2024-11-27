<?php

namespace App\Http\Collections;

use Carbon\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;

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
        ];
    }

    /**
     * Render user and its access tokens
     ** Defines what app features, user can access within UI
     * Access tokens are defined according backend logic
     *
     * @param object $user
     * @return array
     */
    static public function render_user_access(object $access): array
    {
        return [
            'access_token' => $access->access_token,
            'quantity' => $access->quantity,
            'expiration_date' => Carbon::parse($access->expiration_date)->format('Y-m-d')
        ];
    }
}