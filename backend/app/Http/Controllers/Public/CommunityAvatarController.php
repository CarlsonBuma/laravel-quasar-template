<?php

namespace App\Http\Controllers\Public;

use App\Models\UserAvatar;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Middleware\AccessCommunity;
use App\Http\Collections\AvatarCollection;
use App\Http\Collections\AwardCollection;
use App\Http\Collections\CollaboratorCollection;
use App\Models\Collaborators;
use App\Models\AppAwards;

class CommunityAvatarController extends Controller
{
    /**
     * Load public avatar profile [avatar_id]
     *
     * @param Request $request
     * @return void
     */
    public function getPublicAvatar(Request $request) 
    {
        $accessibleAvatar = null;
        $collaborations = null;
        $data = $request->validate([
            'avatar_id' => ['required', 'numeric'],
        ]);
        
        $avatar = UserAvatar::find((int) $data['avatar_id']);
        if($avatar && AccessCommunity::avatarIsAccessible($avatar)) {
            $accessibleAvatar = AvatarCollection::render_community_avatar($avatar);
            $collaborations = Collaborators::join('user_entity_collaborations', 'collaborators.collaboration_id', '=', 'user_entity_collaborations.id')
                ->join('app_awards', 'user_entity_collaborations.award_id', '=', 'app_awards.id')
                ->where('collaborators.user_id', $avatar->user_id)
                ->where('collaborators.is_public', true)
                ->whereNotNull('collaborators.date_requested')
                ->whereNotNull('collaborators.date_released')
                ->whereNull('collaborators.archived')
                ->select('collaborators.*', 'app_awards.id as award_id')
                ->orderBy('collaborators.period_start', 'desc')
                ->get()
                ->groupBy('award_id')
                ->map(function ($collaborators, $award_id) {
                    return [
                        'award' => AwardCollection::render_public_award(AppAwards::find($award_id)),
                        'collaborations' => $collaborators->map(function($collaborator) {
                            return CollaboratorCollection::render_community_collaborator(Collaborators::find($collaborator->id));
                        })
                    ];
                })->filter()->values();
        }
        
        return response()->json([
            'user_collaborations' => $collaborations,
            'avatar' => $accessibleAvatar,
            'message' => 'Avatar loaded.',
        ], 200);
    }
}
