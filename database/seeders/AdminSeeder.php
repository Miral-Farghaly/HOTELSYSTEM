<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create default admin user
        $admin = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@hotel.com',
            'password' => Hash::make('Admin@123!'),
            'email_verified_at' => now(),
            'phone' => '+1234567890',
            'address' => 'Hotel Administration Office',
        ]);

        // Assign admin role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $admin->assignRole($adminRole);
        }

        // Create backup admin (recommended for production)
        $backupAdmin = User::create([
            'name' => 'Backup Administrator',
            'email' => 'backup.admin@hotel.com',
            'password' => Hash::make('BackupAdmin@123!'),
            'email_verified_at' => now(),
            'phone' => '+1234567891',
            'address' => 'Hotel Administration Office',
        ]);

        if ($adminRole) {
            $backupAdmin->assignRole($adminRole);
        }
    }
} 