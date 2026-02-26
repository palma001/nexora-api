<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Company;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 0. Seed Permissions
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
        ]);

        // 1. Create a Master User
        $user = User::factory()->create([
            'name' => 'Admin User',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'company_config_pending' => true,
        ]);

        // 2. Create a Company for this user (simulating onboarding completion)
        // We'll use the service explicitly or just factory if service is complex to invoke here
        // But since we want to trigger Observers (which create Admin Role, Branch, etc.), 
        // using Eloquent create on Company is best.
        
        $company = Company::create([
            'owner_id' => $user->id,
            'name' => 'Admin Corp',
            'tax_id' => '123456789',
            'currency' => 'USD',
            'country' => 'USA',
        ]);
        
        // Observer should have run:
        // - Created Main Branch
        // - Created Admin Role
        // - Assigned User to Company with Admin Role
        
        // Let's refresh user to get current_company_id set by observer/listener if implemented, 
        // or we set it manually as the Observer might not update the User model instance directly in memory
        
        $user->refresh();
        if (!$user->current_company_id) {
            $user->update([
                'current_company_id' => $company->id,
                'company_config_pending' => false
            ]);
        }

        // 3. Create generic users
        User::factory(5)->create();

        // 4. Create default categories
        \App\Models\Category::firstOrCreate(['name' => 'General']);
        \App\Models\Category::firstOrCreate(['name' => 'Bebidas']);
        \App\Models\Category::firstOrCreate(['name' => 'Alimentos']);
    }
}
