<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndUserSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $adminRole = Role::create(['name' => 'admin']);
        $technicianRole = Role::create(['name' => 'technician']);
        $clientRole = Role::create(['name' => 'client']);

        $admin = User::create([
            'name' => 'Admin Helpdesk',
            'email' => 'admin@helpdesk.com',
            'password' => Hash::make('password'),
        ]);

        $admin->assignRole($adminRole);
    }
}
