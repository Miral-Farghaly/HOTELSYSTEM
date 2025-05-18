<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User management
            'view users',
            'create users',
            'edit users',
            'delete users',
            
            // Room management
            'view rooms',
            'create rooms',
            'edit rooms',
            'delete rooms',
            
            // Reservation management
            'view reservations',
            'create reservations',
            'edit reservations',
            'delete reservations',
            'approve reservations',
            
            // Payment management
            'view payments',
            'process payments',
            'refund payments',
            
            // Report management
            'view reports',
            'generate reports',
            
            // Feedback management
            'view feedback',
            'respond feedback',
            'delete feedback',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        
        // Super Admin
        $superAdminRole = Role::create(['name' => 'super-admin']);
        $superAdminRole->givePermissionTo(Permission::all());

        // Hotel Manager
        $managerRole = Role::create(['name' => 'manager']);
        $managerRole->givePermissionTo([
            'view users',
            'view rooms',
            'edit rooms',
            'view reservations',
            'approve reservations',
            'view payments',
            'view reports',
            'generate reports',
            'view feedback',
            'respond feedback',
        ]);

        // Staff
        $staffRole = Role::create(['name' => 'staff']);
        $staffRole->givePermissionTo([
            'view rooms',
            'view reservations',
            'create reservations',
            'edit reservations',
            'view payments',
            'process payments',
            'view feedback',
        ]);

        // Guest
        $guestRole = Role::create(['name' => 'guest']);
        $guestRole->givePermissionTo([
            'view rooms',
            'create reservations',
            'view reservations',
        ]);
    }
} 