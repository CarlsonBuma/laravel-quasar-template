<?php

namespace App\Http\Controllers\UserEntity;

use Carbon\Carbon;
use App\Models\User;
use App\Models\AppAwards;
use App\Models\UserEntity;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Collaborators;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\UserEntityCollaborations;
use App\Http\Collections\AwardCollection;
use App\Http\Collections\EntityCollection;
use App\Http\Middleware\AccessCollaboration;
use Illuminate\Validation\ValidationException;
use App\Http\Collections\CollaboratorCollection;

/**********************************
 * Important Flags
 ** Flags set by User
 *  > 'date_requested' (Date)
 *  > 'date_confirmed' (Date)
 * 
 ** Flags set by entity
 *  > 'date_released' (Date)
 *  > 'date_issued' (Date)
 ***********************************/
class EntityCollaboratorsController extends Controller
{
    /**
     ** Load collaborations and its award
     * Include Custom Entity Awards
     *  > Is Public = false
     *  > Entity_ID set
     *  > is not archived
     *
     * @param Request $request
     * @return void
     */
    public function loadActiveRewardCollaborators(Request $request)
    {
        $data = $request->validate([
            'award_id' => ['required', 'numeric'],
        ]);

        $entity = UserEntity::where('user_id', Auth::id())->first();
        $award = AppAwards::where('id', $data['award_id'])
            ->where('is_public', true)
            ->whereNull('archived')
            ->first();

        if($entity && $award) {
            return response()->json([
                'award' => AwardCollection::render_entity_award_collaborations($award, $entity->id),
                'entity' => EntityCollection::render_community_entity($entity),
                'message' => 'Active collaborations.',
            ], 200);
        }
        
        return response()->json([
            'message' => 'Invalid request.',
        ], 422);
    }

    /**
     ** Load archived Award collaborations
     *
     * @param Request $request
     * @return void
     */
    public function loadClosedRewardCollaborators(Request $request)
    {
        $data = $request->validate([
            'award_id' => ['required', 'numeric'],
        ]);

        $entity = UserEntity::where('user_id', Auth::id())->first();
        $award = AppAwards::where('id', $data['award_id'])
            ->where('is_public', true)
            ->whereNull('archived')
            ->first();

        if($entity && $award) {
            return response()->json([
                'award' => AwardCollection::render_entity_archived_collaborations($award, $entity->id),
                'entity' => EntityCollection::render_community_entity($entity),
                'message' => 'Archived collaborations.',
            ], 200);
        }
        
        return response()->json([
            'message' => 'Invalid request.',
        ], 422);
    }

    /**
     * Search Collaborators
     * Afterwards follows releaseCollaborationToNewCollaborator
     *
     * @param Request $request
     * @return void
     */
    public function searchNewCollaborator(Request $request)
    {
        $data = $request->validate([
            'collaboration_id' => ['required', 'numeric'],
            'email' => ['required', 'string'],
        ]);

        $collaboration = UserEntityCollaborations::where([
            'id' => $data['collaboration_id'],
            'entity_id' => UserEntity::where('user_id', Auth::id())->first()->id
        ])->first();

        if(!$collaboration) {
            return response()->json([
                'message' => 'Invalid request.',
            ], 422);
        }

        $user = User::where('email', $data['email'])->first();
        if(!$user?->has_avatar?->is_community) {
            return response()->json([
                'message' => 'User does not exits.',
            ], 422);
        }

        $closedAmountOfCollaborations = Collaborators::where([
            'collaboration_id' => $collaboration->id,
            'user_id' => $user->id,
        ])->whereNotNull('date_confirmed')->count();

        $ongoingAmountOfCollaborations = Collaborators::where([
            'collaboration_id' => $collaboration->id,
            'user_id' => $user->id,
        ])->whereNull('date_confirmed')->count();

        return response()->json([
            'collaborator' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'name' => $user->name,
                    'collaborations' => $closedAmountOfCollaborations,
                    'ongoing_collaborations' => $ongoingAmountOfCollaborations
                ],
            'message' => 'Collaborators.',
        ], 200);
    }

    /**
     * Release Collaboration to new Collaborator
     * Initialize Start of colaboration
     * Collaboration requested by entity
     *
     * @param Request $request
     * @return void
     */
    public function releaseCollaborationToNewCollaborator(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'numeric'],
            'collaboration_id' => ['required', 'numeric']
        ]);

        $collaboration = $this->validateCollaborationRequest($data['user_id'], $data['collaboration_id']);


        $newCollaborator = Collaborators::create([
            'entity_id' => $collaboration->entity_id,
            'collaboration_id' => $collaboration->id,
            'user_id' => $data['user_id'],
            'date_released' => now(),
        ]);

        $newCollaborator->token = $newCollaborator->id . '-' . Str::random(32);
        $newCollaborator->save();

        return response()->json([
            'collaborator' => CollaboratorCollection::render_entity_collaborator($newCollaborator),
            'message' => 'Collaboration has been released.',
        ], 200);
    }

    /**
     * Release collaboration to collaborator
     * Initialize Start of colaboration
     * Collaboration has been requested by collaborator
     *
     * @param Request $request
     * @return void
     */
    public function releaseCollaborationToCollaborator(Request $request)
    {
        $data = $request->validate([
            'collaborator_id' => ['required', 'numeric']
        ]);

        // Valide Request
        $collaborator = $this->validateCollaboratorRequest($data['collaborator_id']);
        if($collaborator->date_requested && !$collaborator->date_released) {
            $collaborator->date_released = now();
            $collaborator->period_start = now();
            $collaborator->save();
            return response()->json([
                'date_released' => Carbon::parse($collaborator->date_released)->format('Y-m-d'),
                'message' => 'Collaboration has been released.',
            ], 200);
        }   

        return response()->json([
            'message' => 'Invalid request.',
        ], 422);
    }

    /**
     * Issue ongoing collaboration to collaborator
     * Initialize End of colaboration
     *
     * @param Request $request
     * @return void
     */
    public function issueCollaborationToCollaborator(Request $request)
    {
        $data = $request->validate([
            'collaborator_id' => ['required', 'numeric'],
        ]);

        // Valide Request
        $collaborator = $this->validateCollaboratorRequest($data['collaborator_id']);
        if($collaborator->date_requested && $collaborator->date_released &&  !$collaborator->date_issued) {
            $collaborator->date_issued = now();
            $collaborator->save();

            return response()->json([
                'date_issued' => Carbon::parse($collaborator->date_issued)->format('Y-m-d'),
                'message' => 'Collaboration has been issued.',
            ], 200);
        }   

        return response()->json([
            'message' => 'Invalid request.',
        ], 422);
    }

    /**
     * Reopen closed collaboration
     *  > Must be date_issued
     *  > No date_confirmed by collaborator
     *
     * @param Request $request
     * @return void
     */
    public function reopenCollaborator(Request $request)
    {
        $data = $request->validate([
            'collaborator_id' => ['required', 'numeric'],
        ]);

        // Valide Request
        $collaborator = $this->validateCollaboratorRequest($data['collaborator_id']);
        if($collaborator->date_issued && !$collaborator->date_confirmed) {
            $collaborator->date_issued = null;
            $collaborator->save();

            return response()->json([
                'message' => 'Collaboration has been reopened.',
            ], 200);
        }   

        return response()->json([
            'message' => 'Invalid request.',
        ], 422);
    }

    /**
     * Remove Collaboaration from collaboration
     *  > Restict if date_confirmed
     *
     * @param Request $request
     * @return void
     */
    public function deleteCollaborator(Request $request)
    {
        $data = $request->validate([
            'collaborator_id' => ['required', 'numeric']
        ]);

        $collaborator = $this->validateCollaboratorRequest($data['collaborator_id']);
        if($collaborator->date_confirmed) {
            return response()->json([
                'message' => 'Collaboration has been confirmed already.',
            ], 422);
        }

        $collaborator->delete();
        return response()->json([
            'message' => 'Collaborator has been removed.',
        ], 200);
    }

    /**
     * Validate Collaborator Request
     *
     * @param integer $collaboratorID
     * @return object
     */
    private function validateCollaboratorRequest(int $collaboratorID): object
    {
        $collaborator = Collaborators::where([
            'id' => $collaboratorID,
            'entity_id' => UserEntity::where('user_id', Auth::id())->first()->id
        ])->first();

        if($collaborator) return $collaborator;
        throw ValidationException::withMessages([
            'message' => 'Invalid request.',
        ]);
    }

    /**
     * Check if user exists
     *  > Uaer Avatar must be public accessible
     *  > Ther should not be any collaborations ongoing 'date_confirmed'
     * Check if collaboration is accessbile
     *  > Check collaboration access
     *
     * @param integer $userID
     * @param integer $collaborationID
     * @return object|null
     */
    private function validateCollaborationRequest(int $userID, int $collaborationID): ?object
    {
        // User Profile must be public
        $user = User::find($userID);
        if(!$user?->has_avatar?->is_community) {
            throw ValidationException::withMessages([
                'message' => 'User does not exist.',
            ]);
        };

        // Validate Collaboration
        $collaboration = UserEntityCollaborations::where([
            'id' => $collaborationID,
            'entity_id' => UserEntity::where('user_id', Auth::id())->first()?->id
        ])->first();

        // Check Accessible Collaboration
        if(!AccessCollaboration::checkAccess($collaboration)) {
            throw ValidationException::withMessages([
                'message' => 'Please adjust your public access limits.',
            ]);
        }

        // Check if ongoing collaborations are in progress
        if(Collaborators::where([
            'collaboration_id' => $collaboration->id,
            'user_id' => $user->id
        ])->whereNull('date_confirmed')->exists()) {
            throw ValidationException::withMessages([
                'message' => 'Ongoing callaborations already existing.',
            ]);
        }

        return $collaboration;
    }
}
