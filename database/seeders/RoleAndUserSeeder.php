<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class RoleAndUserSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

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