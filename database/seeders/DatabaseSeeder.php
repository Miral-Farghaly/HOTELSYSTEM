<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Room;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // Create roles
        $adminRole = Role::create(['name' => 'admin']);
        $customerRole = Role::create(['name' => 'customer']);
        $receptionistRole = Role::create(['name' => 'receptionist']);

        // Create permissions
        $permissions = [
            'manage-rooms',
            'manage-bookings',
            'manage-users',
            'manage-payments',
            'view-reports',
            'manage-maintenance',
            'manage-staff'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Assign permissions to roles
        $adminRole->givePermissionTo($permissions);
        $receptionistRole->givePermissionTo([
            'manage-bookings',
            'manage-maintenance',
            'view-reports'
        ]);

        // Create admin user
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@hotel.com',
            'password' => bcrypt('password')
        ]);
        $admin->assignRole('admin');

        // Create receptionist
        $receptionist = User::factory()->create([
            'name' => 'Receptionist User',
            'email' => 'receptionist@hotel.com',
            'password' => bcrypt('password')
        ]);
        $receptionist->assignRole('receptionist');

        // Create room types
        $roomTypes = [
            'standard' => [
                'base_price' => 100.00,
                'capacity' => 2,
                'amenities' => ['wifi', 'tv', 'air_conditioning']
            ],
            'deluxe' => [
                'base_price' => 200.00,
                'capacity' => 3,
                'amenities' => ['wifi', 'tv', 'air_conditioning', 'minibar', 'safe']
            ],
            'suite' => [
                'base_price' => 350.00,
                'capacity' => 4,
                'amenities' => ['wifi', 'tv', 'air_conditioning', 'minibar', 'safe', 'kitchen', 'balcony']
            ]
        ];

        // Create sample rooms
        foreach ($roomTypes as $type => $details) {
            for ($i = 1; $i <= 5; $i++) {
                Room::create([
                    'number' => $type[0] . str_pad($i, 2, '0', STR_PAD_LEFT),
                    'type' => $type,
                    'floor' => ceil($i/2),
                    'description' => ucfirst($type) . ' room with modern amenities',
                    'price_per_night' => $details['base_price'],
                    'base_price' => $details['base_price'],
                    'capacity' => $details['capacity'],
                    'amenities' => $details['amenities'],
                    'is_available' => true,
                    'needs_maintenance' => false
                ]);
            }
        }
    }
}
