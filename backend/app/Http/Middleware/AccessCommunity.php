<?php

namespace App\Http\Middleware;

use App\Models\Collaborators;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\AccessBusinessCockpit;

class AccessCommunity
{
    /**
     * Check if current user-avatar is accessible
     *  > By Public
     *  > Or by Owner
     * 
     * @param object | null $avatar
     * @return boolean
     */
    static public function avatarIsAccessible(object | null $avatar): bool
    {
        if ($avatar && $avatar->is_community) return true;
        
        else if (
            $avatar
            && Auth::guard('api')->check()
            && Auth::guard('api')->user()->id === $avatar->user_id
        ) return true;

        return false;
    }

    /**
     * Check if current Entity is accessible
     *  > By Public
     *  > Or by Owner
     * Definitions of Access in here!
     *
     * @param object|null $entity
     * @param string $accessToken
     * @return boolean
     */
    static public function entityIsAccessible(object $entity = null): bool
    {
        if (
            $entity 
            && $entity->name
            && $entity->is_community
            && $entity->avatar

            // Check if entity-user has access,
            // According access-token
            && AccessUser::checkUserAccessByToken($entity->user_id,  AccessBusinessCockpit::$accessToken)
        ) return true;
        else if (
            $entity
            && Auth::guard('api')->check()
            && Auth::guard('api')->user()->id === $entity->user_id
        ) return true;
        return false;
    }

    /**
     * Collaboration is accessible
     *  > 'is_public', !'archived'
     *  > Entity must exist
     *  > Active collaborator does not exists
     *  > Limit not exceeding, if not '0'
     *      > Check Flag 'date_issued', by entity
     *
     * @param object|null $collaboration
     * @param int $user
     * @return bool
     */
    static public function collaborationIsAccessible(object $collaboration = null, int $userID = 0): bool
    {
        if(
            !$collaboration 
            || !$collaboration->is_public 
            || !$collaboration->entity_id
            || $collaboration->archived
        ) return false;
        
        // Collaborator already existing
        if($userID && Collaborators::where([
            'collaboration_id' => $collaboration->id,
            'user_id' => $userID,
        ])->whereNull('date_confirmed')->exists()) return false;

        // Limit Check
        if($collaboration->access_limit !== 0) {
            $collaboratorsCount = $collaboration
                ->has_collaborators()
                ->whereNull('archived')
                ->whereNull('date_issued')
                ->count();
            if($collaboratorsCount >= $collaboration->access_limit) 
                return false;
        }
        
        return true;
    }
}
