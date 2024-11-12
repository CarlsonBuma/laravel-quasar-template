<?php

namespace App\Http\Collections;

use Carbon\Carbon;

abstract class CollaboratorCollection
{
    /**
     * Entity gets it's collaborator
     *
     * @param object $collaborator
     * @return array
     */
    static public function render_entity_collaborator(object $collaborator): array
    {
        $user = $collaborator->belongs_to_user;
        return [
            '_type' => 'Object $EntityCollaboratorCollection',
            'id' => $collaborator->id,
            'user_id' => $collaborator->user_id,
            'entity_id' => $collaborator->entity_id,
            'date_released' => $collaborator->date_released ? Carbon::parse($collaborator->date_released)->format('Y-m-d') : null,
            'date_requested' => $collaborator->date_requested ? Carbon::parse($collaborator->date_requested)->format('Y-m-d') : null,
            'date_issued' => $collaborator->date_issued ? Carbon::parse($collaborator->date_issued)->format('Y-m-d') : null,
            'date_confirmed' => $collaborator->date_confirmed ? Carbon::parse($collaborator->date_confirmed)->format('Y-m-d') : null,
            'period_start' => $collaborator->period_start ? Carbon::parse($collaborator->period_start)->format('Y-m-d') : null,
            'period_duration' => $collaborator->period_duration,
            'archived' => $collaborator->archived ? Carbon::parse($collaborator->archived)->format('Y-m-d') : null,
            'token' => $collaborator->token,
            'user' => [
                'id' => $user->id,
                'email' => $user->email, 
                'name' => $user->name, 
            ],
        ];
    }

    /**
     * Authorized User Access
     *
     * @param object|null $collaborator
     * @return array
     */
    static public function render_user_collaborator(object $collaborator = null): array
    {
        if(!$collaborator) return [];
        return [
            '_type' => 'Object $UserCollaboratorCollection',
            'collaboration' => CollaborationCollection::render_user_collaboration($collaborator->belongs_to_collaboration),
            'id' => $collaborator->id,
            'user_id' => $collaborator->user_id,
            'entity_id' => $collaborator->entity_id,
            'is_public' => $collaborator->is_public,
            'date_released' => $collaborator->date_released ? Carbon::parse($collaborator->date_released)->format('Y-m-d') : null,
            'date_requested' => $collaborator->date_requested ? Carbon::parse($collaborator->date_requested)->format('Y-m-d') : null,
            'date_issued' => $collaborator->date_issued ? Carbon::parse($collaborator->date_issued)->format('Y-m-d') : null,
            'date_confirmed' => $collaborator->date_confirmed ? Carbon::parse($collaborator->date_confirmed)->format('Y-m-d') : null,
            'period_start' => $collaborator->period_start ? Carbon::parse($collaborator->period_start)->format('Y-m-d') : null,
            'period_duration' => $collaborator->period_duration,
            'archived' => $collaborator->archived ? Carbon::parse($collaborator->archived)->format('Y-m-d') : null,
            'token' => $collaborator->token,
        ];
    }

    /**
     * Community Collaborator Details
     *
     * @param object|null $collaborator
     * @return array
     */
    static public function render_community_collaborator(object $collaborator = null): array
    {
        if(!$collaborator) return [];
        return [
            '_type' => 'Object $CommunityCollaboratorCollection',
            'id' => $collaborator->id,
            'is_public' => $collaborator->is_public,
            'date_issued' => $collaborator->date_issued ? Carbon::parse($collaborator->date_issued)->format('Y-m-d') : null,
            'date_confirmed' => $collaborator->date_confirmed ? Carbon::parse($collaborator->date_confirmed)->format('Y-m-d') : null,
            'period_start' => $collaborator->period_start ? Carbon::parse($collaborator->period_start)->format('Y-m-d') : null,
            'period_duration' => $collaborator->period_duration,
            'collaboration' => CollaborationCollection::render_community_user_collaboration($collaborator->belongs_to_collaboration)
        ];
    }

    /**
     * Render User Collaborations
     *  > $type
     *  > $entity
     *  > $collaboration
     *  > $collaborator
     *
     * @param object $collaborator
     * @return array
     */
    static public function render_collaboration(object $collaborator): array
    {
        return [
            '_type' => 'Object $UserAwardCollaborationCollection',
            'type' => $collaborator->belongs_to_collaboration->belongs_to_award,
            'entity' => EntityCollection::render_community_entity($collaborator->belongs_to_entity),
            'collaboration' => CollaborationCollection::render_user_collaboration($collaborator->belongs_to_collaboration),
            'collaborator' => SELF::render_user_collaborator($collaborator)
        ];
    }
}