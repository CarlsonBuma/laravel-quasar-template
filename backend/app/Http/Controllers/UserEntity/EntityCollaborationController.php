<?php

namespace App\Http\Controllers\UserEntity;

use App\Models\AppAwards;
use App\Models\AppSkills;
use App\Models\UserEntity;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\PivotCollaborationSkills;
use App\Models\UserEntityCollaborations;
use App\Http\Collections\AwardCollection;
use App\Http\Collections\EntityCollection;
use Illuminate\Validation\ValidationException;


class EntityCollaborationController extends Controller
{
    /**
     * Load Entity Awards
     *
     * @return void
     */
    public function loadAwards($all = false) 
    {
        $entity = UserEntity::where('user_id', Auth::id())->first();
        $publicAwards = AppAwards::where('is_public', true)
            ->orderBy('label')
            ->get()
            ->map(function ($award) use($entity, $all) {
                if($all) 
                    return AwardCollection::render_entity_award($award, $entity->id);
                elseif($award->has_collaborations->where('entity_id', $entity->id)->count() > 0)
                    return AwardCollection::render_entity_award($award, $entity->id);
            })->filter()->values();
        
        return response()->json([
            'entity' => EntityCollection::render_entity_impressum($entity),
            'public_awards' => $publicAwards,
            'message' => 'Entity awards loaded.',
        ], 200);
    }

    /**
     ** Create new Collaboration
     *  > Check public awards
     *  > Check entity awards
     *
     * @param Request $request
     * @return void
     */
    public function createCollaboration(Request $request)
    {
        $data = $request->validate([
            "award_id" => ['required', 'numeric'],
            "departement_id" => ['nullable', 'numeric'],
            "title" => ['required', 'string', 'max:99'],
            "meta" => ['nullable', 'string', 'max:99'],
            "about" => ['required', 'string', 'max:499'],
            
            "tasks" => ['nullable', 'array'],
            "skills" => ['nullable', 'array'],
            "duration" => ['nullable', 'string', 'max:99'],
            "details" => ['nullable', 'string', 'max:499'],
            "is_public" => ['required', 'boolen'],
            "access_limit" => ['nullable', 'numeric'],
            "tags" => ['nullable', 'array'],
        ]);

        $award = AppAwards::find($data['award_id']);
        $collaboration = UserEntityCollaborations::create([
            'entity_id' => Auth::user()->has_entity?->id,
            'award_id' => $award?->id,
            'departement_id' => (int) $data['departement_id'],
            'title' => $data['title'],
            'meta' => $data['meta'],
            'about' => $data['about'],
            
            'tasks' => $data['tasks'],
            'duration' => $data['duration'],
            'details' => $data['details'],
            'is_public' => $data['is_public'],
            'access_limit' => $data['access_limit'],
            'tags' => $data['tags'],
            'token' => null,
            'archived' => null,
        ]);

        $this->connectSkillsWithCollaboration($data['skills'], $collaboration->id);
        $collaboration->token = $collaboration->id . '-' . Str::random(32);
        $collaboration->save();

        return response()->json([
            'collaboration' => $collaboration,
            'message' => 'New collaboration created.',
        ], 200);
    }
    
    /**
     ** Make Collaboration Public,
     * So collaborators can access it, by themself
     *
     * @param Request $request
     * @return void
     */
    public function updatePublicity(Request $request)
    {
        $data = $request->validate([
            'collaboration_id' => ['required', 'numeric'],
            'is_public' => ['required', 'boolean'],
        ]);

        $collaboration = $this->validateCollaboration($data['collaboration_id']);
        $collaboration->update([
            'is_public' => $data['is_public']
        ]);

        return response()->json([
            'message' => $data['is_public'] 
                ? 'Collaboration is public accessible.' 
                : 'Collaboration is set to private.',
        ], 200);
    }

    /**
     ** Update Limit
     *  > Access Flag, 0 => unlimited access
     *
     * @param Request $request
     * @return void
     */
    public function updateLimit(Request $request)
    {
        $data = $request->validate([
            'collaboration_id' => ['required', 'numeric'],
            'limit' => ['nullable', 'numeric'],
        ]);

        $collaboration = $this->validateCollaboration($data['collaboration_id']);
        $collaboration->update([
            'access_limit' => $data['limit'] ?? 0
        ]);

        return response()->json([
            'message' => 'Limit has been updated.',
        ], 200);
    }

    /**
     ** Update Limit
     *  > Access Flag, 0 => unlimited access
     *
     * @param Request $request
     * @return void
     */
    public function updateTags(Request $request)
    {
        $data = $request->validate([
            'collaboration_id' => ['required', 'numeric'],
            'tags' => ['nullable', 'array'],
        ]);

        if(count($data['tags']) > 9) {
            return response()->json([
                'message' => 'Only 9 tags allowed.',
            ], 422);
        }

        $collaboration = $this->validateCollaboration($data['collaboration_id']);
        $collaboration->update([
            'tags' => $data['tags'] ?? null
        ]);

        return response()->json([
            'message' => 'SEO tags has been updated.',
        ], 200);
    }

    /**
     ** Archive Collaboration
     *
     * @param Request $request
     * @return void
     */
    public function archiveCollaboration(Request $request)
    {
        $data = $request->validate([
            'collaboration_id' => ['required', 'numeric'],
        ]);

        // Check restriction
        $collaboration = $this->validateCollaboration($data['collaboration_id']);
        $collaboration->update([
            'archived' => now()
        ]);

        return response()->json([
            'message' => 'Collaboration has been archived.',
        ], 200);
    }

    /**
     ** Unarchive Collaboration
     *
     * @param Request $request
     * @return void
     */
    public function unarchiveCollaboration(Request $request)
    {
        $data = $request->validate([
            'collaboration_id' => ['required', 'numeric'],
        ]);

        $collaboration = $this->validateCollaboration($data['collaboration_id']);
        $collaboration->update([
            'archived' => null
        ]);

        return response()->json([
            'message' => 'Collaboration has been unarchived.',
        ], 200);
    }

    /**
     ** Delete Collaboration
     *  > Hard Delete, if no collaborations is 
     *      > not 'date_confirmed' by any collaborator
     *  > Softdelete (remove entity_id, from collaboration)
     *      > Only if no ongoing collaborationns
     *          > All active collaboration has been issued to active collaborators
     *          > has 'date_released'
     *          > has 'date_issued'
     *  
     *  > Restrict on "Collaborations ongoing"
     *
     * @param Request $request
     * @return void
     */
    public function deleteCollaboration(Request $request)
    {
        $data = $request->validate([
            'collaboration_id' => ['required', 'numeric'],
        ]);

        // Hard delete
        // Only if no collaboration has been issued to collaborators
        $collaboration = $this->validateCollaboration($data['collaboration_id']);
        if(!$collaboration
            ->has_collaborators()
            ->whereNotNull('date_confirmed')
            ->exists()
        ) {
            $collaboration->delete();
            return response()->json([
                'message' => 'Collaboration has been deleted.',
            ], 200);
        }

        // Softdelete
        // Check if no ongoing collaboration
        if(!$collaboration->has_collaborators()
            ->whereNotNull('date_released')
            ->whereNull('date_issued')
            ->exists()
        ) {
            $collaboration->entity_id = null;
            $collaboration->save();
            return response()->json([
                'message' => 'Collaboration has been removed.',
            ], 200);
        }

        return response()->json([
            'message' => 'Active collaborators must be rewarded first.',
        ], 422);
    }

     /**
     * Validate requested collaboration
     *
     * @param integer $collaborationID
     * @return object|null
     */
    private function validateCollaboration(int $collaborationID = 0): ?object
    {
        $collaboration = null;
        $entity = UserEntity::where('user_id', Auth::id())->first();
        if($collaborationID && $entity) {
            $collaboration =  UserEntityCollaborations::where([
                'id' => $collaborationID,
                'entity_id' => $entity->id
            ])->first();
        }

        if($collaboration) return $collaboration;
        throw ValidationException::withMessages([
            'message' => 'Invalid request.',
        ]);
    }

    /**
     * Add skills to collaboration
     *
     * @param array $skills
     * @param integer $collaborationID
     * @return void
     */
    private function connectSkillsWithCollaboration(array $skills, int $collaborationID): void
    {
        foreach($skills as $skill){
            $trimmedSkillLabel = ucwords(strtolower($skill['label']));
            $comparedSkill = AppSkills::firstOrCreate([
                'label' => $trimmedSkillLabel
            ]);

            PivotCollaborationSkills::updateOrCreate([
                'collaboration_id' => $collaborationID,
                'skill_id' => $comparedSkill->id
            ], [
                'description' => $skill['description']
            ]);
        }
    }
}
