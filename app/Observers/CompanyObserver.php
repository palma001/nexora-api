<?php

namespace App\Observers;

use App\Models\Company;
use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CompanyObserver
{
    /**
     * Handle the Company "created" event.
     */
    public function created(Company $company): void
    {
        // 1. Create Default Main Branch
        Branch::create([
            'company_id' => $company->id,
            'name' => 'Sucursal Principal',
            'is_main' => true,
        ]);

        // 2. Create Admin Role
        $adminRole = Role::create([
            'company_id' => $company->id,
            'name' => 'Admin',
            'slug' => 'admin',
            'description' => 'Administrator with full access',
        ]);

        // 2b. Create Default Permissions and attach to Admin Role
        $permissions = [
            'products.view', 'products.create', 'products.update', 'products.delete',
            'categories.view', 'categories.create', 'categories.update', 'categories.delete',
            'branches.view', 'branches.create', 'branches.update', 'branches.delete',
            'sales.view', 'sales.create',
            'reports.view',
            'settings.view', 'settings.update',
        ];

        foreach ($permissions as $permName) {
            $permission = \App\Models\Permission::firstOrCreate(['name' => $permName]);
            $adminRole->permissions()->attach($permission->id);
        }

        // 3. Assign Owner to Company with Admin Role
        $owner = $company->owner;
        if ($owner) {
            $company->users()->attach($owner->id, [
                'role_id' => $adminRole->id,
                'is_owner' => true,
                'status' => 'active',
            ]);

            // 4. Update User Context (if not already set, or force update)
            if (!$owner->current_company_id) {
                $owner->current_company_id = $company->id;
                $owner->company_config_pending = false;
                $owner->save();
            }
        }
    }
}
