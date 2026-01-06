<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Buat role jika belum ada
        $kasirRole = Role::firstOrCreate(['name' => 'kasir']);
        $ownerRole = Role::firstOrCreate(['name' => 'owner']);

        // Buat user kasir
        $kasir = User::firstOrCreate(
            ['email' => 'kasir@example.com'],
            [
                'name' => 'Kasir Bengkel',
                'password' => Hash::make('password'), // ganti kalau mau
            ]
        );
        $kasir->assignRole($kasirRole);

        // Buat user owner
        $owner = User::firstOrCreate(
            ['email' => 'owner@example.com'],
            [
                'name' => 'Owner Bengkel',
                'password' => Hash::make('password'), // ganti kalau mau
            ]
        );
        $owner->assignRole($ownerRole);
    }
}
