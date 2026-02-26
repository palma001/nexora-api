<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Productos
            ['name' => 'products.view', 'description' => 'Ver lista de productos y detalles'],
            ['name' => 'products.create', 'description' => 'Crear nuevos productos'],
            ['name' => 'products.update', 'description' => 'Editar información de productos'],
            ['name' => 'products.delete', 'description' => 'Eliminar productos'],
            
            // Categorías
            ['name' => 'categories.view', 'description' => 'Ver categorías de productos'],
            ['name' => 'categories.create', 'description' => 'Crear nuevas categorías'],
            ['name' => 'categories.update', 'description' => 'Editar categorías'],
            ['name' => 'categories.delete', 'description' => 'Eliminar categorías'],
            
            // Sucursales
            ['name' => 'branches.view', 'description' => 'Ver sucursales de la empresa'],
            ['name' => 'branches.create', 'description' => 'Agregar nuevas sucursales'],
            ['name' => 'branches.update', 'description' => 'Editar información de sucursales'],
            ['name' => 'branches.delete', 'description' => 'Eliminar sucursales'],
            
            // Ventas
            ['name' => 'sales.view', 'description' => 'Ver historial de ventas'],
            ['name' => 'sales.create', 'description' => 'Realizar nuevas ventas (POS)'],
            
            // Reportes
            ['name' => 'reports.view', 'description' => 'Acceder a reportes de ventas y stock'],
            
            // Equipo (Usuarios, Roles y Permisos)
            ['name' => 'team.view', 'description' => 'Ver miembros del equipo y roles'],
            ['name' => 'team.manage', 'description' => 'Gestionar usuarios, roles y asignar permisos'],
            
            // Configuración e Integraciones
            ['name' => 'settings.view', 'description' => 'Ver configuraciones e integraciones'],
            ['name' => 'settings.update', 'description' => 'Modificar configuraciones de la empresa'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission['name']],
                ['description' => $permission['description']]
            );
        }
    }
}
