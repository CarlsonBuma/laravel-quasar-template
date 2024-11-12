<?php

namespace App\Http\Controllers\User;

use App\Models\AppAwards;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Collaborators;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\UserEntityCollaborations;
use App\Http\Collections\AwardCollection;
use App\Http\Collections\CollaboratorCollection;
use App\Http\Middleware\AccessCommunity;


/**********************************
 * UserCollaborations, where Collaborators.user_id
 ** Users Flags
 *  > 'date_requested' (Date)
 *  > 'date_confirmed' (Date)
 * 
 ** Entity Flags
 *  > 'date_released' (Date)
 *  > 'date_issued' (Date)
 *
 * @return void
 **********************************/
class UserCollaboratorController extends Controller
{
    /**
     * Load confirmed rewards
     *  > $Rewards[collaborator]
     *  > Flag: date_confirmed && date_issued != null
     *
     * @param Request $request
     * @return void
     */
    public function loadReleasedRewards()
    {
        $collaborations = Collaborators::join('user_entity_collaborations', 'collaborators.collaboration_id', '=', 'user_entity_collaborations.id')
            ->join('app_awards', 'user_entity_collaborations.award_id', '=', 'app_awards.id')
            ->where('collaborators.user_id', Auth::id())
            ->whereNotNull('collaborators.date_requested')
            ->whereNotNull('collaborators.date_released')
            ->whereNull('collaborators.archived')
            ->select('collaborators.*', 'app_awards.id as award_id')
            ->orderBy('collaborators.period_start', 'desc')
            ->get()
            ->groupBy('award_id')
            ->map(function ($collaborators, $award_id) {
                return [
                    'award' => AwardCollection::render_user_award(AppAwards::find($award_id)),
                    'collaborations' => $collaborators->map(function($collaborator) {
                        return CollaboratorCollection::render_user_collaborator(Collaborators::find($collaborator->id));
                    })
                ];
            })->filter()->values();
        
        return response()->json([
            'collaborations' => $collaborations,
            'message' => 'Active collaborations.',
        ], 200);
    }

    /**
     * User agrees to new collaboration
     * Collaboration initialized by collaborator (user)
     *
     * @param Request $request
     * @return void
     */
    public function requestNewCollaboration(Request $request) 
    {
        $data = $request->validate([
            "collaboration_id" => ['required', 'numeric'],
        ]);

        $collaboration = UserEntityCollaborations::find($data['collaboration_id']);
        if(AccessCommunity::collaborationIsAccessible($collaboration, Auth::id())) {
            $newCollaborator = Collaborators::create([
                'user_id' => Auth::id(),
                'collaboration_id' => $collaboration->id,
                'entity_id' => $collaboration->entity_id,
                'date_requested' => now()
            ]);
    
            $newCollaborator->token = $newCollaborator->id . '-' . Str::random(32);
            $newCollaborator->save();
    
            return response()->json([
                'message' => 'Participation has been requested.',
            ], 200); 
        }

        return response()->json([
            'message' => 'Collaboration already in progress.',
        ], 422);
    }
    
    /**
     * User agree collaboraiton
     * Collaboration initialized by owner (entity)
     *
     * @param Request $request
     * @return void
     */
    public function agreeRequestedCollaboration(Request $request) 
    {
        $data = $request->validate([
            "collaborator_id" => ['required', 'numeric'],
        ]);

        $collaborator = $this->checkCollaborator($data['collaborator_id']);
        if($collaborator && !$collaborator->date_requested) {
            $collaborator->date_requested = now();
            $collaborator->period_start = now();
            $collaborator->save();
            return response()->json([
                'message' => 'New collaboration has been initialized.',
            ], 200);
        }

        return response()->json([
            'message' => 'Invalid request.',
        ], 422); 
    }

    /**
     * User agree collaboration
     *  > Flag 'date_issued' must be set (Type: Date), by Entity
     *
     * @param Request $request
     * @return void
     */
    public function confirmCollaboration(Request $request) 
    {
        $data = $request->validate([
            "collaborator_id" => ['required', 'numeric'],
            'period_start' => ['required', 'string', 'max:99'],
            'period_duration' => ['required', 'string', 'max:99'],
        ]);

        $collaborator = $this->checkReleasedCollaborator($data['collaborator_id']);
        if($collaborator && $collaborator->date_issued) {
            $collaborator->period_start = $data['period_start'];
            $collaborator->period_duration = $data['period_duration'];
            $collaborator->date_confirmed = now();
            $collaborator->save();
            
            return response()->json([
                'message' => 'Collaboration closed.',
            ], 200);
        }

        return response()->json([
            'message' => 'Invalid request.',
        ], 422);        
    }

    /**
     * User agree collaboration
     *  > Flag 'date_issued' must be set (Type: Date), by Entity
     *
     * @param Request $request
     * @return void
     */
    public function publishCollaboration(Request $request) 
    {
        $data = $request->validate([
            "collaborator_id" => ['required', 'numeric'],
            "is_public" => ['required', 'boolean']
        ]);

        if($collaborator = $this->checkReleasedCollaborator($data['collaborator_id'])) {
            $collaborator->is_public = (bool) $data['is_public'];
            $collaborator->save();
            return response()->json([
                'message' => (bool) $data['is_public']
                    ? 'Collaboration published.'
                    : 'Collaboration set to private.',
            ], 200);
        }

        return response()->json([
            'message' => 'Invalid request.',
        ], 422);        
    }

    /**
     * Update Collaborator Duration
     *
     * @param Request $request
     * @return void
     */
    public function updateStart(Request $request) 
    {
        $data = $request->validate([
            "collaborator_id" => ['required', 'numeric'],
            "start" => ['required', 'string', 'max:99'],
        ]);

        if($collaborator = $this->checkReleasedCollaborator($data['collaborator_id'])) {
            $collaborator->period_start = $data['start'];
            $collaborator->save();
            return response()->json([
                'message' => 'Start of collaboration has been updated.'
            ], 200);
        }

        return response()->json([
            'message' => 'Invalid request.',
        ], 422); 
    }

    /**
     * Update Collaborator Duration
     *
     * @param Request $request
     * @return void
     */
    public function updateDuration(Request $request) 
    {
        $data = $request->validate([
            "collaborator_id" => ['required', 'numeric'],
            "duration" => ['required', 'string', 'max:99'],
        ]);

        $collaborator = $this->checkReleasedCollaborator($data['collaborator_id']);
        if($collaborator->date_confirmed) {
            $collaborator->period_duration = $data['duration'];
            $collaborator->save();
            return response()->json([
                'message' => 'Duration of collaboration has been updated.'
            ], 200);
        }

        return response()->json([
            'message' => 'Invalid request.',
        ], 422); 
    }

    /**
     * Remove Collaboration
     *  > No restrictions
     *
     * @param Request $request
     * @return void
     */
    public function removeCollaboration(Request $request)
    {
        $data = $request->validate([
            "collaborator_id" => ['required', 'numeric'],
        ]);

        if($collaborator = $this->checkCollaborator($data['collaborator_id'])) {
            $collaborator->delete();
            return response()->json([
                'message' => 'Collaboration has been removed.',
            ], 200);
        }
        
        return response()->json([
            'message' => 'Invalid request.',
        ], 422); 
    }

    /**
     * Check Confirmed collaboraotr
     *
     * @param integer $collaboratorID
     * @return object|null
     */
    private function checkCollaborator(int $collaboratorID): ?object
    {
        return Collaborators::where([
            'id' => $collaboratorID,
            'user_id' => Auth::id()
        ])->first();
    }

    /**
     * Check Confirmed collaboraotr
     *
     * @param integer $collaboratorID
     * @return object|null
     */
    private function checkReleasedCollaborator(int $collaboratorID): ?object
    {
        return Collaborators::where([
            'id' => $collaboratorID,
            'user_id' => Auth::id()
        ])->whereNotNull('date_released')
            ->whereNotNull('date_requested')
            ->first();
    }
}
