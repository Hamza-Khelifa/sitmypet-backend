<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Roles
        $superAdminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $moderatorRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'moderator', 'guard_name' => 'web']);
        
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'pet-owner', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'pet-sitter', 'guard_name' => 'web']);

        // Create Default Super Admin
        $superAdmin = User::factory()->create([
            'email' => 'admin@sitmypet.fr',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
        ]);
        
        $superAdmin->assignRole($superAdminRole);
    }
}
