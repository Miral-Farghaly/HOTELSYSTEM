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

        // Seed roles and permissions
        $this->call(RolesAndPermissionsSeeder::class);
        
        // Seed room types
        $this->call(RoomTypeSeeder::class);
        
        // Seed configurations
        $this->call(ConfigurationSeeder::class);
        
        // Create admin user
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@hotel.com',
            'password' => bcrypt('password')
        ]);
        $admin->assignRole('admin');

        // Create staff user
        $staff = User::factory()->create([
            'name' => 'Staff User',
            'email' => 'staff@hotel.com',
            'password' => bcrypt('password')
        ]);
        $staff->assignRole('staff');

        // Create sample rooms for each room type
        $roomTypes = ['Standard Single', 'Standard Double', 'Deluxe Suite', 'Family Room', 'Presidential Suite'];
        $floors = [1, 1, 2, 2, 3];
        
        foreach ($roomTypes as $index => $type) {
            $floor = $floors[$index];
            for ($i = 1; $i <= 3; $i++) {
                $roomNumber = ($floor * 100) + ($index * 10) + $i;
                Room::create([
                    'number' => $roomNumber,
                    'type' => $type,
                    'floor' => $floor,
                    'description' => "Modern $type room with excellent amenities",
                    'price_per_night' => $this->getPriceForType($type),
                    'base_price' => $this->getPriceForType($type),
                    'currency' => 'USD',
                    'capacity' => $this->getCapacityForType($type),
                    'is_available' => true,
                    'needs_maintenance' => false
                ]);
            }
        }
    }

    private function getPriceForType(string $type): float
    {
        return match($type) {
            'Standard Single' => 100.00,
            'Standard Double' => 150.00,
            'Deluxe Suite' => 300.00,
            'Family Room' => 250.00,
            'Presidential Suite' => 500.00,
            default => 100.00,
        };
    }

    private function getCapacityForType(string $type): int
    {
        return match($type) {
            'Standard Single' => 1,
            'Standard Double' => 2,
            'Deluxe Suite' => 3,
            'Family Room' => 4,
            'Presidential Suite' => 4,
            default => 2,
        };
    }
}
