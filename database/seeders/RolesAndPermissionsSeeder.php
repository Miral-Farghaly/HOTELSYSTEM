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
        $permissions = [
            // Room permissions
            'view rooms',
            'create rooms',
            'edit rooms',
            'delete rooms',
            'manage room maintenance',
            
            // Booking permissions
            'view bookings',
            'create bookings',
            'edit bookings',
            'cancel bookings',
            'manage booking payments',
            
            // User permissions
            'view users',
            'create users',
            'edit users',
            'delete users',
            
            // Report permissions
            'view reports',
            'generate reports',
            'export reports',
            
            // Settings permissions
            'manage settings',
            'manage roles',
            'view logs',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        Role::create(['name' => 'guest'])
            ->givePermissionTo(['view rooms']);

        Role::create(['name' => 'customer'])
            ->givePermissionTo([
                'view rooms',
                'create bookings',
                'view bookings',
                'cancel bookings',
            ]);

        Role::create(['name' => 'staff'])
            ->givePermissionTo([
                'view rooms',
                'edit rooms',
                'view bookings',
                'edit bookings',
                'cancel bookings',
                'manage booking payments',
                'view users',
                'view reports',
            ]);

        Role::create(['name' => 'manager'])
            ->givePermissionTo([
                'view rooms',
                'create rooms',
                'edit rooms',
                'delete rooms',
                'manage room maintenance',
                'view bookings',
                'create bookings',
                'edit bookings',
                'cancel bookings',
                'manage booking payments',
                'view users',
                'create users',
                'edit users',
                'view reports',
                'generate reports',
                'export reports',
                'manage settings',
            ]);

        Role::create(['name' => 'admin'])
            ->givePermissionTo(Permission::all());
    }
} 