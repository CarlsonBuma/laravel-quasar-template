<?php

namespace App\Http\Middleware;

class AccessCollaboration
{
    /**
     * Collaboration is accessible
     *  > !'archived'
     *  > Entity must exist
     *  > Active collaborator does not exists
     *  > Limit not exceeding, if not '0'
     *      > Check Flag 'date_issued', by entity
     *
     * @param object|null $collaboration
     * @param int $user
     * @return bool
     */
    static public function checkAccess(object $collaboration = null): bool
    {
        if(!$collaboration || $collaboration->archived) return false;

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
