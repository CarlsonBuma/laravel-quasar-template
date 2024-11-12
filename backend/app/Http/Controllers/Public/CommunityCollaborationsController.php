<?php

namespace App\Http\Controllers\Public;

use Carbon\Carbon;
use App\Models\AppSkills;
use App\Models\AppAwards;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\UserEntityCollaborations;
use App\Http\Collections\CollaborationCollection;
use App\Http\Collections\SkillCollection;

class CommunityCollaborationsController extends Controller
{
    /**
     ** Search Logic:
     *  entity_collaboraiton.public
     *  && entity_collaboration.archived === null
     *  && user_entity.is_community
     *  && (
     *      searchQuery includes collaboration.title
     *      || searchQuery includes app_skills
     *  )
     * && $collaborationIsAccessible
     * 
     ** Return
     *  > matching collaboration
     *  > matching skills 
     *
     * @param Request $request
     * @return void
     */
    public function searchPublicCollaborations(Request $request)
    {
        $skills = [];
        $data = $request->validate([
            'award_id' => ['nullable', 'numeric'],
            'searchQuery' => ['nullable', 'array'],
        ]);
        
        // Setup Model
        $collaborationModel = UserEntityCollaborations::where('user_entity_collaborations.is_public', true)
            ->join('user_entity', 'user_entity_collaborations.entity_id', '=', 'user_entity.id')
            ->whereNull('user_entity_collaborations.archived')
            ->where('user_entity.is_community', true);
        
        // Compare collaboration type
        if (isset($data['award_id'])) {
            $award = AppAwards::find((int) $data['award_id']);
            if ($award?->is_public) {
                $collaborationModel = $collaborationModel->where('user_entity_collaborations.award_id', $award->id);
            }
        }
        
        // Compare Keywords and Skills
        if (isset($data['searchQuery']) && count($data['searchQuery']) > 0) {
            $collaborationModel = $collaborationModel->where(function($query) use ($data) {
                // Check Collaboration.title and Collaboration.tasks
                $query->where(function($query) use ($data) {
                    foreach ($data['searchQuery'] as $searchTerm) {
                        $query->orWhere('user_entity_collaborations.title', 'ILIKE', '%' . $searchTerm . '%')
                            ->orWhere('user_entity_collaborations.tags', 'ILIKE', '%' . $searchTerm . '%');
                    }
                });

                // Check Skills
                $query->orWhere(function($query) use ($data) {
                    $query->whereExists(function($subQuery) use ($data) {
                        $subQuery->select(DB::raw(1))
                            ->from('pivot_collaboration_skills')
                            ->join('app_skills', 'pivot_collaboration_skills.skill_id', '=', 'app_skills.id')
                            ->whereColumn('pivot_collaboration_skills.collaboration_id', 'user_entity_collaborations.id')
                            ->where(function($subQuery) use ($data) {
                                foreach ($data['searchQuery'] as $searchTerm) {
                                    $subQuery->orWhere('app_skills.label', 'ILIKE', '%' . $searchTerm . '%');
                                }
                            });
                    });
                });
            });

            // Matching Skills
            $skills = AppSkills::where(function($query) use ($data) {
                foreach ($data['searchQuery'] as $term) {
                    $query->orWhere('label', 'ILIKE', '%' . $term . '%');
                }
            })->orderBy('label')
            ->get()
            ->map(function($skill) {
                return SkillCollection::render_public_collaboration_skills($skill);    
            })->filter()->values();
        }
        
        // Finalize Search results
        $searchResults = $collaborationModel
            ->distinct()
            ->select('user_entity_collaborations.*')
            ->orderBy('user_entity_collaborations.date_start')
            ->get()
            ->map(function ($collaboration) {
                return CollaborationCollection::render_community_entity_collaboration(UserEntityCollaborations::find($collaboration->id));
            })->filter()->values();        
        
        return response()->json([
            'matching_skills' => $skills,
            'collaborations' => $searchResults->toArray(),
            'message' => 'Search results.',
        ], 200);
    }
}
