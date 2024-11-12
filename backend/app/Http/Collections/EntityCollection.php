<?php

namespace App\Http\Collections;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;

abstract class EntityCollection
{
    public static $entity = [
        '_type' => 'Object $EntityCollection',
        'id' => 0,
        'entity_id' => 0,
        'is_community' => false,
        'avatar' => '',
        'name' => '',
        'slogan' => '',
        'foundation' => '',
        'about' => '',
        'tags' => [],
        'contact' => '',
        'website' => '',
        'bin' => '',
        'location' => null,
        'collaborations' => []
    ];

    /**
     * Entity Impressum
     *  > Accessible if is_community
     *  > Or user is owner
     *
     * @param object|null $entity
     * @param boolean $isOwner
     * @return array
     */
    static public function render_entity_impressum(object $entity = null, bool $isOwner = false): array
    {
        if(!$entity || (!$entity->is_community && !$isOwner)) return SELF::$entity;
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
            '_type' => 'Object $EntityImpressumCollection',
            'id' => $entity->id,
            'is_community' => $entity->is_community,
            'avatar' => $avatarPath,
            'name' => $entity->name,
            'slogan' => $entity->slogan,
            'about' => $entity->about,
            'tags' => $entityTags,
            'contact' => $entity->contact,
            'website' => $entity->website,
            'country' => $country,
            'location' => $geoLocation,
        ];
    }

    /**
     * Community Entity Collection
     *  > Accessible if is_community
     *
     * @param object|null $entity
     * @return array
     */
    static public function render_community_entity(object $entity = null): array
    {
        //* Restriction: Entity must be community
        if(!$entity || !$entity->is_community) return SELF::$entity;
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
            '_type' => 'Object $EntityCommunityCollection',
            'id' => $entity->id,
            'entity_id' => $entity->id,             // Obsolete
            'is_community' => $entity->is_community,
            'avatar' => $avatarPath,
            'name' => $entity->name,
            'slogan' => $entity->slogan,
            'about' => $entity->about,
            'tags' => $entityTags,
            'contact' => $entity->contact,
            'website' => $entity->website,
            'country' => $country,
            'location' => $geoLocation,
        ];
    }
}