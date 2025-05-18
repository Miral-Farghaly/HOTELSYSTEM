<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        $configurations = [
            [
                'key' => 'hotel_name',
                'value' => 'Luxury Hotel & Resorts',
                'type' => 'string',
                'group' => 'general',
            ],
            [
                'key' => 'hotel_address',
                'value' => '123 Luxury Street, Resort City, 12345',
                'type' => 'string',
                'group' => 'general',
            ],
            [
                'key' => 'contact_email',
                'value' => 'contact@luxuryhotel.com',
                'type' => 'string',
                'group' => 'contact',
            ],
            [
                'key' => 'contact_phone',
                'value' => '+1-234-567-8900',
                'type' => 'string',
                'group' => 'contact',
            ],
            [
                'key' => 'check_in_time',
                'value' => '14:00',
                'type' => 'time',
                'group' => 'booking',
            ],
            [
                'key' => 'check_out_time',
                'value' => '11:00',
                'type' => 'time',
                'group' => 'booking',
            ],
            [
                'key' => 'max_booking_days',
                'value' => '30',
                'type' => 'integer',
                'group' => 'booking',
            ],
            [
                'key' => 'min_advance_booking_days',
                'value' => '1',
                'type' => 'integer',
                'group' => 'booking',
            ],
            [
                'key' => 'cancellation_policy_hours',
                'value' => '24',
                'type' => 'integer',
                'group' => 'booking',
            ],
            [
                'key' => 'tax_rate',
                'value' => '10',
                'type' => 'float',
                'group' => 'payment',
            ],
            [
                'key' => 'service_charge',
                'value' => '5',
                'type' => 'float',
                'group' => 'payment',
            ],
            [
                'key' => 'currency',
                'value' => 'USD',
                'type' => 'string',
                'group' => 'payment',
            ],
        ];

        foreach ($configurations as $config) {
            DB::table('configurations')->insertOrIgnore($config);
        }
    }
} 