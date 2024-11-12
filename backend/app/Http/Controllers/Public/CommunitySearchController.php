<?php

namespace App\Http\Controllers\Public;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Collections\EntityCollection;
use App\Http\Middleware\AccessBusinessCockpit;


class CommunitySearchController extends Controller
{
    /**
     * Search Entitites
     *  > Services @JSON_Stringigied 
     *  > Geolocation attributes
     *
     * @param Request $request
     * @return void
     */
    public function searchPublicEntitites(Request $request) {
        $data = $request->validate([
            'geolocation_set' => ['required', 'boolean'],
            'geolocation_lat' => ['nullable', 'numeric'],
            'geolocation_lng' => ['nullable', 'numeric'],
            'geolocation_radius' => ['nullable', 'numeric'],
        ]);

        $geolocationSet = (bool) $data['geolocation_set'];
        $geolocationLat = $data['geolocation_lat'];
        $geolocationLng = $data['geolocation_lng'];
        $geolocationRadius = $data['geolocation_radius'];        // Distance in [m]

        // Collection of all accessible entities
        // with active Subscription
        $accessibleEntities = null;
        $accessibleEntities = AccessBusinessCockpit::getAccessibleEntities(AccessBusinessCockpit::$accessToken);

        // Geolocation
        if($geolocationSet) {
            // Calculate geolocation radius
            $latDelta = $geolocationRadius / 111111; // 1 degree of latitude is approximately 111111 meters
            $lngDelta = $geolocationRadius / (111111 * cos(deg2rad($geolocationLat))); // 1 degree of longitude is approximately 111111 meters at the equator, but varies with latitude
            $minLat = $geolocationLat - $latDelta;
            $maxLat = $geolocationLat + $latDelta;
            $minLng = $geolocationLng - $lngDelta;
            $maxLng = $geolocationLng + $lngDelta;
            
            // Serach between values
            $accessibleEntities->join('app_geolocations', 'user_entity.location_id', '=', 'app_geolocations.id')
                ->whereBetween('app_geolocations.lat', [$minLat, $maxLat])
                ->whereBetween('app_geolocations.lng', [$minLng, $maxLng])
                ->select('user_entity.*');
        }

        // Make Collection
        // Calculate Matching SearchQuery
        // And add results to analytics
        $entityCollection = [];
        $accessibleEntities = $accessibleEntities->inRandomOrder()->get();
        foreach ($accessibleEntities as $entity) {
            array_push($entityCollection, EntityCollection::render_community_entity($entity));
        };

        // Sort according to matches
        uasort($entityCollection, function ($a, $b) {
            return $b['matching'] - $a['matching'];
        });
        
        // Array Reindexing
        // Caused by sort for matching services
        $entityCollection = array_values($entityCollection);

        return response()->json([
            'entity_collection' => $entityCollection,
            'message' => 'Matching entities.',
        ], 200);
    }
}
