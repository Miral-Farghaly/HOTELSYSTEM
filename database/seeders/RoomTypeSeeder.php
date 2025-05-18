<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RoomType;

class RoomTypeSeeder extends Seeder
{
    public function run(): void
    {
        $roomTypes = [
            [
                'name' => 'Standard Single',
                'description' => 'Comfortable room with a single bed, perfect for solo travelers',
                'base_price' => 100.00,
                'capacity' => 1,
                'amenities' => json_encode([
                    'Wi-Fi',
                    'TV',
                    'Air Conditioning',
                    'Private Bathroom',
                    'Work Desk'
                ]),
            ],
            [
                'name' => 'Standard Double',
                'description' => 'Spacious room with a double bed, ideal for couples',
                'base_price' => 150.00,
                'capacity' => 2,
                'amenities' => json_encode([
                    'Wi-Fi',
                    'TV',
                    'Air Conditioning',
                    'Private Bathroom',
                    'Work Desk',
                    'Mini Fridge'
                ]),
            ],
            [
                'name' => 'Deluxe Suite',
                'description' => 'Luxury suite with separate living area and premium amenities',
                'base_price' => 300.00,
                'capacity' => 3,
                'amenities' => json_encode([
                    'Wi-Fi',
                    'Smart TV',
                    'Climate Control',
                    'Luxury Bathroom',
                    'Work Space',
                    'Mini Bar',
                    'Coffee Machine',
                    'Room Service',
                    'City View'
                ]),
            ],
            [
                'name' => 'Family Room',
                'description' => 'Spacious room designed for families with multiple beds',
                'base_price' => 250.00,
                'capacity' => 4,
                'amenities' => json_encode([
                    'Wi-Fi',
                    'TV',
                    'Air Conditioning',
                    'Private Bathroom',
                    'Mini Fridge',
                    'Extra Storage',
                    'Child Safety Features'
                ]),
            ],
            [
                'name' => 'Presidential Suite',
                'description' => 'Our most luxurious accommodation with premium services and amenities',
                'base_price' => 500.00,
                'capacity' => 4,
                'amenities' => json_encode([
                    'Wi-Fi',
                    'Smart Home System',
                    'Climate Control',
                    'Luxury Bathroom with Jacuzzi',
                    'Private Office',
                    'Full Bar',
                    'Premium Coffee Machine',
                    '24/7 Butler Service',
                    'Panoramic View',
                    'Private Dining Area'
                ]),
            ],
        ];

        foreach ($roomTypes as $roomType) {
            RoomType::create($roomType);
        }
    }
} 