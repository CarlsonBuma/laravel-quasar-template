<?php

namespace App\Http\Collections;

use App\Http\Middleware\AccessCommunity;

abstract class SkillCollection
{
    /**
     * Render skills, 
     * that belongs to accessible collaborations
     *
     * @param object|null $skill
     * @return array
     */
    static public function render_public_collaboration_skills(object $skill = null): array
    {
        if(!$skill) return [];
        $collaborations = $skill->has_collaborations
            ->map(function($pivot) {
                $collaboration = $pivot->belongs_to_collaboration;
                if(AccessCommunity::collaborationIsAccessible($collaboration)) {
                    return CollaborationCollection::render_community_entity_collaboration($collaboration);
                }
                return [];
            });

        if(count($collaborations) === 0) return [];
        return [
            'id' => $skill->id,
            'label' => $skill->label,
            'description' => $skill->description,
            'is_public' => $skill->is_public,
            'active_collaborations' => $collaborations,
        ];
    }
}