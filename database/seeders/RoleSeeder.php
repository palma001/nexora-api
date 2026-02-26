<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use App\Models\Company;
use Illuminate\Database\Seeder;

/**
 * Seeder for default roles and permission assignments.
 */
class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = Company::first();
        if (!$company) return;

        // Admin Role
        $admin = Role::updateOrCreate(
            ['company_id' => $company->id, 'slug' => 'admin'],
            ['name' => 'Administrador', 'description' => 'Acceso total a la gestión de la empresa']
        );
        $admin->permissions()->sync(Permission::all());

        // Seller Role
        $seller = Role::updateOrCreate(
            ['company_id' => $company->id, 'slug' => 'vendedor'],
            ['name' => 'Vendedor', 'description' => 'Acceso a ventas y consulta de productos']
        );
        $seller->permissions()->sync(
            Permission::whereIn('name', [
                'products.view',
                'categories.view',
                'sales.create',
                'sales.view'
            ])->get()
        );
    }
}
