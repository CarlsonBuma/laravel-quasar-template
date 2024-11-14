<?php

namespace App\Http\Collections;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;

abstract class EntityCollection
{
    /**
     * Default collection
     *
     * @var array
     */
    public static $entity = [
        '_type' => 'Collection $entity',
        'id' => 0,
        'is_public' => false,
        'name' => '',
        'avatar' => '',
        'about' => '',
        'contact' => '',
        'website' => '',
        'location' => null,
        'country_id' => null,
        'tags' => [],
    ];

    /**
     * Users entity collection
     *  > Accessible if is_public
     *  > Or user is owner
     *
     * @param object|null $entity
     * @param boolean $isOwner
     * @return array
     */
    static public function render_user_entity(object $entity = null): array
    {
        if(!$entity) return SELF::$entity;
        
        $entityTags = $entity->tags;
        $geoLocation = $entity->location_id
            ? GeolocationCollection::render_geoLoaction($entity->belongs_to_location, $showAddress = true)
            : GeolocationCollection::$geoLocation;
        $avatarPath = $entity->avatar
            ? URL::to(Storage::url('entityAvatar')) . '/' . $entity->avatar
            : '';
        $country = $entity->country_id
            ? [
                'id' => $entity->country_id,
                'name' => $entity->belongs_to_country->name,
                'code' => $entity->belongs_to_country->code,
            ] : null;
        
        return [
            '_type' => 'Collection $entity',
            'id' => $entity->id,
            'is_public' => $entity->is_public,
            'name' => $entity->name,
            'avatar' => $avatarPath,
            'about' => $entity->about,
            'contact' => $entity->contact,
            'website' => $entity->website,
            'location' => $geoLocation,
            'country' => $country,
            'tags' => $entityTags,
        ];
    }
}