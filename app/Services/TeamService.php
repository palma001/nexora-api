<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

/**
 * Service to handle team-related logic including users, roles and permissions.
 */
class TeamService
{
    /**
     * Create a new user and associate them with a company and role.
     *
     * @param array $data
     * @param int $companyId
     * @return User
     */
    public function createUser(array $data, int $companyId): User
    {
        return DB::transaction(function () use ($data, $companyId) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'username' => $data['username'],
                'password' => Hash::make($data['password']),
                'current_company_id' => $companyId,
            ]);

            $user->companies()->attach($companyId, [
                'status' => $data['status'] ?? 'active',
                'is_owner' => $data['is_owner'] ?? false,
            ]);

            if (!empty($data['role_ids'])) {
                $user->rolesInCompany($companyId)->syncWithPivotValues($data['role_ids'], [
                    'company_id' => $companyId
                ]);
            } elseif (!empty($data['role_id'])) {
                $user->rolesInCompany($companyId)->syncWithPivotValues([$data['role_id']], [
                    'company_id' => $companyId
                ]);
            }

            if (!empty($data['permissions'])) {
                $user->directPermissions()->syncWithPivotValues($data['permissions'], [
                    'company_id' => $companyId
                ]);
            }

            return $user;
        });
    }

    /**
     * Update a user's pivot data in a company.
     *
     * @param User $user
     * @param int $companyId
     * @param array $data
     * @return void
     */
    public function updateUserInCompany(User $user, int $companyId, array $data): void
    {
        $pivotData = array_filter($data, function ($key) {
            return in_array($key, ['status', 'is_owner']);
        }, ARRAY_FILTER_USE_KEY);

        // Update User basic info
        $userData = array_filter($data, function ($key) {
            return in_array($key, ['name', 'email', 'username']);
        }, ARRAY_FILTER_USE_KEY);

        if (!empty($userData)) {
            $user->update($userData);
        }

        if (isset($data['password']) && !empty($data['password'])) {
            $user->update(['password' => Hash::make($data['password'])]);
        }

        if (!empty($pivotData)) {
            $user->companies()->updateExistingPivot($companyId, $pivotData);
        }

        if (isset($data['role_ids'])) {
            $user->rolesInCompany($companyId)->syncWithPivotValues($data['role_ids'], [
                'company_id' => $companyId
            ]);
        } elseif (isset($data['role_id'])) {
            $user->rolesInCompany($companyId)->syncWithPivotValues([$data['role_id']], [
                'company_id' => $companyId
            ]);
        }

        if (isset($data['permissions'])) {
            $user->directPermissions()->syncWithPivotValues($data['permissions'], [
                'company_id' => $companyId
            ]);
        }
    }

    /**
     * Create a new role with permissions.
     *
     * @param array $data
     * @param int $companyId
     * @return Role
     */
    public function createRole(array $data, int $companyId): Role
    {
        return DB::transaction(function () use ($data, $companyId) {
            $role = Role::create([
                'company_id' => $companyId,
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'description' => $data['description'] ?? null,
            ]);

            if (!empty($data['permissions'])) {
                $role->permissions()->sync($data['permissions']);
            }

            return $role;
        });
    }

    /**
     * Update an existing role.
     *
     * @param Role $role
     * @param array $data
     * @return Role
     */
    public function updateRole(Role $role, array $data): Role
    {
        return DB::transaction(function () use ($role, $data) {
            if (isset($data['name'])) {
                $role->name = $data['name'];
                $role->slug = Str::slug($data['name']);
            }
            
            if (isset($data['description'])) {
                $role->description = $data['description'];
            }
            
            $role->save();

            if (isset($data['permissions'])) {
                $role->permissions()->sync($data['permissions']);
            }

            return $role;
        });
    }
}
