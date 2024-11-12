<?php

namespace App\Http\Collections;

use App\Http\Collections\CollaborationCollection;

abstract class AwardCollection
{
    /**
     * Entity Award
     *
     * @param object $award
     * @param integer $entityID
     * @return array
     */
    static public function render_public_award(object $award): array
    {
        if(!$award || !$award->is_public) return [];
        return [
            '_type' => 'Object $PublicAwardCollection',
            'id' => $award->id,
            'is_public' => $award->is_public,
            'label' => $award->label,
            'description' => $award->description,
            'credits' => $award->credits,
            'evaluation' => $award->evaluation,
            'icon' => $award->icon,
        ];
    }

    /**
     * Entity Award
     *
     * @param object $award
     * @param integer|null $entityID
     * @return array
     */
    static public function render_entity_award(object $award, int $entityID = null): array
    {
        return [
            '_type' => 'Object $EntityAwardCollection',
            'id' => $award->id,
            'is_public' => $award->is_public,
            'label' => $award->label,
            'description' => $award->description,
            'credits' => $award->credits,
            'evaluation' => $award->evaluation,
            'icon' => $award->icon,
            'open_requests' => $entityID
                ? $award->has_collaborations()
                    ->join('collaborators', 'user_entity_collaborations.id', '=', 'collaborators.collaboration_id')
                    ->where('user_entity_collaborations.entity_id', $entityID)
                    ->whereNull('user_entity_collaborations.archived')
                    ->whereNull('collaborators.date_released')
                    ->whereNotNull('collaborators.date_requested')
                    ->count()
                : 0,
            'active_collaborators' => $entityID
                ? $award->has_collaborations()
                    ->join('collaborators', 'user_entity_collaborations.id', '=', 'collaborators.collaboration_id')
                    ->where('user_entity_collaborations.entity_id', $entityID)
                    ->whereNull('user_entity_collaborations.archived')
                    ->whereNull('collaborators.date_issued')
                    ->count()
                : 0,
            'active_collaborations' => $entityID
                ? $award->has_collaborations()
                    ->where('entity_id', $entityID)
                    ->whereNull('archived')
                    ->count()
                : 0,
            'archived_collaborations' => $entityID
                ? $award->has_collaborations()
                    ->where('entity_id', $entityID)
                    ->whereNotNull('archived')
                    ->count()
                : 0,
        ];
    }

    /**
     * Render Award Collaborations
     *
     * @param object $award
     * @param integer $entityID
     * @return array
     */
    static public function render_entity_award_collaborations(object $award, int $entityID): array
    {
        return [
            '_type' => 'Object $EntityAwardCollaborationsCollection',
            'type' => $award,
            'collaborations' => $award?->has_collaborations()->where([
                    'entity_id' => $entityID,
                    'award_id' => $award->id
                ])->whereNull('archived')
                ->orderBy('date_start')
                ->get()
                ->map(function ($collaboration) {
                    return CollaborationCollection::render_entity_collaboration($collaboration);
                })
        ];
    }

    /**
     * Render Award Collaborations
     *
     * @param object $award
     * @param integer|null $entityID
     * @return array
     */
    static public function render_entity_archived_collaborations(object $award, int $entityID = null): array
    {
        return [
            '_type' => 'Object $EntityAwardCollaborationsArchiveCollection',
            'type' => $award,
            'collaborations' => $award->has_collaborations()->where([
                    'entity_id' => $entityID,
                    'award_id' => $award->id
                ])->whereNotNull('archived')
                ->orderBy('created_at')
                ->get()
                ->map(function ($collaboration) {
                    return CollaborationCollection::render_entity_collaboration($collaboration);
                })
        ];
    }

    /**
     * User Award
     *
     * @param object $award
     * @param integer $amount
     * @return array
     */
    static public function render_user_award(object $award): array
    {
        return [
            '_type' => 'Object $UserAwardCollection',
            'id' => $award->id,
            'is_public' => $award->is_public,
            'label' => $award->label,
            'description' => $award->description,
            'credits' => $award->credits,
            'evaluation' => $award->evaluation,
            'icon' => $award->icon,
            'collaboration_count' => $award->collaboration_count
        ];
    }
}