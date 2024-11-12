<?php

namespace App\Http\Controllers\Public;

use App\Models\UserEntity;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Middleware\AccessBusinessCockpit;
use App\Http\Middleware\AccessCommunity;
use App\Http\Collections\EntityCollection;
use App\Http\Collections\CollaborationCollection;

class CommunityEntityController extends Controller
{
    /**
     * Get public entitiy
     *
     * @param Request $request
     * @return void
     */
    public function getPublicEntity(Request $request) 
    {
        $data = $request->validate([
            'entity_id' => ['required', 'numeric'],
        ]);

        $accessibleEntity = null;
        $collaborations = null;
        $entity = UserEntity::find($data['entity_id']);
        if(AccessCommunity::entityIsAccessible($entity)) {
            $accessibleEntity = EntityCollection::render_community_entity($entity);
            $collaborations = $entity->has_collaborations()
                ->where('is_public', true)
                ->whereNull('archived')
                ->orderBy('date_start')
                ->get()
                ->map(function ($collaboration) {
                    return CollaborationCollection::render_community_entity_collaboration($collaboration);
                })->filter()->values();
        }
        
        return response()->json([
            'entity' => $accessibleEntity,
            'collaborations' => $collaborations,
            'message' => 'Entity loaded.',
        ], 200);
    }
    
    /**
     * Get Latest Entitites
     *
     * @return void
     */
    public function getLatestEntities() 
    {
        $entityCollection = [];
        $accessibleEntities = AccessBusinessCockpit::getAccessibleEntities(AccessBusinessCockpit::$accessToken)
            ->inRandomOrder()
            ->take(3)
            ->get();

        foreach ($accessibleEntities as $entity)
            array_push($entityCollection, EntityCollection::render_community_entity($entity)); 

        return response()->json([
            'entities' => $entityCollection,
            'message' => 'Entities loaded.',
        ], 200);
    }
}
