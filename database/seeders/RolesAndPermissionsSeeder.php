<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        Permission::create(['name' => 'manage-rooms']);
        Permission::create(['name' => 'manage-bookings']);
        Permission::create(['name' => 'manage-maintenance']);
        Permission::create(['name' => 'manage-payments']);
        Permission::create(['name' => 'manage-users']);

        // Create roles and assign permissions
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        $staff = Role::create(['name' => 'staff']);
        $staff->givePermissionTo(['manage-rooms', 'manage-bookings', 'manage-maintenance']);

        $customer = Role::create(['name' => 'customer']);
        $customer->givePermissionTo(['manage-bookings']);
    }
} 