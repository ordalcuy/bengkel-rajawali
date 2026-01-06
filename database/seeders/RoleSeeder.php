<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat roles
        $ownerRole = Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $kasirRole = Role::firstOrCreate(['name' => 'kasir', 'guard_name' => 'web']);
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        // Assign owner role ke user pertama (jika ada)
        $firstUser = User::first();
        if ($firstUser && !$firstUser->hasAnyRole(['owner', 'super_admin', 'kasir'])) {
            $firstUser->assignRole('owner');
        }

        $this->command->info('Roles created: owner, kasir, super_admin');
        $this->command->info('First user assigned as owner');
    }
}
