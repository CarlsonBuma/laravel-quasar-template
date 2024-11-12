<?php

namespace App\Http\Controllers\User;

use App\Models\Collaborators;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Collections\CollaborationCollection;


class UserDashboardController extends Controller
{
    /**
     * Active Collaborations, 
     *  > Flags: 'date_requested', 'date_issued', 'date_confirmed'
     *  > Collaboration must be: 
     *      > not 'archived'
     *      > assigned to 'entity'
     *
     * @return void
     */
    public function loadDashboard()
    {
        $baseQuery = Collaborators::join('user_entity_collaborations', 'collaborators.collaboration_id', '=', 'user_entity_collaborations.id')
            ->where('collaborators.user_id', Auth::id())
            ->whereNotNull('user_entity_collaborations.entity_id')
            ->whereNull('user_entity_collaborations.archived')
            ->whereNull('collaborators.archived');

        $collaborationRequests = (clone $baseQuery)
            ->whereNull('collaborators.date_requested')
            ->orderBy('collaborators.date_released')
            ->select('collaborators.*')
            ->get()
            ->map(function ($collaborator) {
                return CollaborationCollection::render_user_collaboration($collaborator->belongs_to_collaboration, $collaborator);
            });

        $collaborationConfirmations = (clone $baseQuery)
            ->whereNotNull('collaborators.date_issued')
            ->whereNull('collaborators.date_confirmed')
            ->orderBy('collaborators.date_issued', 'desc')
            ->select('collaborators.*')
            ->get()
            ->map(function ($collaborator) {
                return CollaborationCollection::render_user_collaboration($collaborator->belongs_to_collaboration, $collaborator);
            });

        $collaborationOngoing = (clone $baseQuery)
            ->whereNotNull('collaborators.date_requested')
            ->whereNull('collaborators.date_issued')
            ->orderBy('user_entity_collaborations.date_start')
            ->select('collaborators.*')
            ->get()
            ->map(function ($collaborator) {
                return CollaborationCollection::render_user_collaboration($collaborator->belongs_to_collaboration, $collaborator);
            });

        $totalCollaborations = (clone $baseQuery)
            ->whereNotNull('date_confirmed')
            ->orderBy('date_released')
            ->count();

        
        return response()->json([
            'total_closed_collaborations' => $totalCollaborations,
            'collaboration_requests' => $collaborationRequests,
            'collaboration_confirmations' =>  $collaborationConfirmations,
            'collaborations_ongoing' => $collaborationOngoing, 
            'message' => 'Collaborations loaded.',
        ], 200);
    }
}
