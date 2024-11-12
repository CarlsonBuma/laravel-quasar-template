<?php

namespace App\Http\Collections;

use App\Models\Collaborators;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\AccessCommunity;
use App\Http\Collections\CollaboratorCollection;

abstract class CollaborationCollection
{
    /**
     * Entity access collaboration
     *  > Entity gets all assigned collaborators
     *
     * @param object|null $collaboration
     * @param boolean $onlyActive
     * @return array
     */
    static public function render_entity_collaboration(object|null $collaboration): array
    {
        if(!$collaboration) return [];
        return [
            '_type' => 'Object $EntityCollaboration',
            'id' => $collaboration->id,
            'award' => $collaboration->belongs_to_award,
            'title' => $collaboration->title,
            'meta' => $collaboration->meta,
            'about' => $collaboration->about,
            'contact' => $collaboration->belongs_to_entity?->contact,
            'skills' => $collaboration->has_skills_pivot->map(function ($pivot) {
                $skill = $pivot->belongs_to_skill;
                return [
                    'skill' => $skill,
                    'label' => $skill->label,
                    'description' => $pivot->description,
                    'pivot' => $pivot,
                ];
            }),

            'duration' => $collaboration->duration,
            'details' => $collaboration->details,
            'access_limit' => $collaboration->access_limit,
            'is_public' => $collaboration->is_public,
            'tags' => $collaboration->tags,
            'token' => $collaboration->token,
            'collaborators' => $collaboration->has_collaborators()
                ->whereNull('archived')
                ->whereNull('date_issued')
                ->orderBy('date_issued', 'desc')
                ->get()
                ->map(function ($pivot) {
                    return CollaboratorCollection::render_entity_collaborator($pivot);
                }),
            'closed_collaborators' => $collaboration->has_collaborators()
                ->whereNull('archived')
                ->whereNotNull('date_issued')
                ->orderBy('date_issued', 'desc')
                ->get()
                ->map(function ($pivot) {
                    return CollaboratorCollection::render_entity_collaborator($pivot);
                })
        ];
    }

    /**
     * User access collaboration
     *  > User as collaborator
     *
     * @param object $collaboration
     * @param object|null $collaborator
     * @return array
     */
    static public function render_user_collaboration(object $collaboration, object $collaborator = null): array
    {
        if(!$collaboration) return [];
        $entity = $collaboration->belongs_to_entity;
        return [
            '_type' => 'Object $UserCollaboration',
            'id' => $collaboration->id,
            'entity' => EntityCollection::render_community_entity($collaboration->belongs_to_entity),
            'award' => $collaboration->belongs_to_award,
            'title' => $collaboration->title,
            'meta' => $collaboration->meta,
            'about' => $collaboration->about,
            'contact' => $collaboration->belongs_to_entity?->is_community 
                ? $collaboration->belongs_to_entity->contact
                : null,
            'skills' => $collaboration->has_skills_pivot->map(function ($pivot) {
                $skill = $pivot->belongs_to_skill;
                return [
                    'skill' => $skill,
                    'label' => $skill->label,
                    'description' => $pivot->description,
                    'pivot' => $pivot,
                ];
            }),

            'duration' => $collaboration->duration,
            'details' => $collaboration->details,
            'collaborator' => CollaboratorCollection::render_user_collaborator($collaborator)
        ];
    }

    /**
     * Community Collaborations
     *  > Check Flag 'is_public'
     *  > Check Flag 'acess_limit' > $amountOfAssignedCollaborators
     *  > Check current UserCollaborator, if no active collaboration exists
     *
     * @param object|null $collaboration
     * @return array
     */
    static public function render_community_entity_collaboration(object $collaboration = null): array
    {
        if(!$collaboration || !AccessCommunity::collaborationIsAccessible($collaboration)) return [];
        return [
            '_type' => 'Object $CommunityCollaborationCollection',
            'is_active_collaborator' => SELF::userIsCollaborator($collaboration->id),
            'id' => $collaboration->id,
            'entity' => EntityCollection::render_entity_impressum($collaboration->belongs_to_entity),
            'award' => $collaboration->belongs_to_award,
            'title' => $collaboration->title,
            'meta' => $collaboration->meta,
            'duration' => $collaboration->duration,
            'about' => $collaboration->about,
            'contact' => $collaboration->belongs_to_entity?->is_community 
                ? $collaboration->belongs_to_entity->contact
                : null,
            'skills' => $collaboration->has_skills_pivot->map(function ($pivot) {
                $skill = $pivot->belongs_to_skill;
                return [
                    'skill' => $skill,
                    'label' => $skill->label,
                    'description' => $pivot->description,
                    'pivot' => $pivot,
                ];
            }),
        ];
    }

    /**
     * Community User Collaborations
     *  > All collaborations of user are rendered
     *  > NO restrictions, as long they belong to user
     *
     * @param object|null $collaboration
     * @return array
     */
    static public function render_community_user_collaboration(object $collaboration = null): array
    {
        if(!$collaboration) return [];
        return [
            '_type' => 'Object $CommunityCollaborationCollection',
            'id' => $collaboration->id,
            'entity' => EntityCollection::render_entity_impressum($collaboration->belongs_to_entity),
            'award' => $collaboration->belongs_to_award,
            'title' => $collaboration->title,
            'meta' => $collaboration->meta,
            'duration' => $collaboration->duration,
            'about' => $collaboration->about,
            'contact' => $collaboration->belongs_to_entity?->is_community 
                ? $collaboration->belongs_to_entity->contact
                : null,
            'skills' => $collaboration->has_skills_pivot->map(function ($pivot) {
                $skill = $pivot->belongs_to_skill;
                return [
                    'skill' => $skill,
                    'label' => $skill->label,
                    'description' => $pivot->description,
                    'pivot' => $pivot,
                ];
            }),
        ];
    }

    /**
     * Check if user is already collaborator
     *
     * @param integer $collaborationID
     * @return boolean
     */
    static private function userIsCollaborator(int $collaborationID): bool
    {
        if($userID = Auth::guard('api')->user()?->id) {
            return Collaborators::where([
                'collaboration_id' => $collaborationID,
                'user_id' => $userID,
            ])->whereNull('date_confirmed')->exists();
        }

        return false;
    }
}