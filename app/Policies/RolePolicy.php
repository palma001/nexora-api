<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Role;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy for Role model management.
 */
class RolePolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view roles.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('team.view') || $user->hasPermission('team.manage');
    }

    /**
     * Determine if the user can create or update roles.
     *
     * @param User $user
     * @return bool
     */
    public function manage(User $user): bool
    {
        return $user->hasPermission('team.manage');
    }
}
