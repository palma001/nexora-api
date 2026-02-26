<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy for User model, specifically for team management actions.
 */
class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view the team members.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('team.view') || $user->hasPermission('team.manage');
    }

    /**
     * Determine if the user can manage team members (create, update roles).
     *
     * @param User $user
     * @return bool
     */
    public function manage(User $user): bool
    {
        return $user->hasPermission('team.manage');
    }

    /**
     * Determine if the user can update a specific team member.
     *
     * @param User $user
     * @param User $targetUser
     * @return bool
     */
    public function update(User $user, User $targetUser): bool
    {
        // One cannot demote themselves if they are the last owner, 
        // but for now let's keep it simple with permission check
        return $user->hasPermission('team.manage');
    }
}
