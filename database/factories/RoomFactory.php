<?php

namespace Database\Factories;

use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomFactory extends Factory
{
    protected $model = Room::class;

    public function definition()
    {
        $price = $this->faker->randomFloat(2, 100, 1000);
        $roomTypes = ['Standard Single', 'Standard Double', 'Deluxe Suite', 'Family Room', 'Presidential Suite'];
        
        return [
            'number' => $this->faker->unique()->numberBetween(100, 999),
            'type' => $this->faker->randomElement($roomTypes),
            'floor' => $this->faker->numberBetween(1, 10),
            'description' => $this->faker->paragraph(),
            'price_per_night' => $price,
            'base_price' => $price,
            'currency' => 'USD',
            'capacity' => $this->faker->numberBetween(1, 6),
            'is_available' => $this->faker->boolean(80),
            'needs_maintenance' => $this->faker->boolean(10),
            'is_maintenance' => false,
            'status' => 'active',
            'is_blocked' => false,
            'block_reason' => null,
            'block_until' => null,
            'allow_waitlist' => $this->faker->boolean(20),
            'max_overbooking' => $this->faker->numberBetween(0, 2),
            'amenities' => $this->faker->randomElements(
                ['wifi', 'tv', 'minibar', 'safe', 'balcony', 'ocean_view', 'bathtub', 'kitchen'],
                $this->faker->numberBetween(3, 6)
            ),
        ];
    }

    public function available()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_available' => true,
                'needs_maintenance' => false,
                'is_maintenance' => false,
                'status' => 'active',
                'is_blocked' => false,
            ];
        });
    }

    public function unavailable()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_available' => false,
                'status' => 'inactive',
            ];
        });
    }

    public function needsMaintenance()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_available' => false,
                'needs_maintenance' => true,
                'is_maintenance' => true,
                'status' => 'maintenance',
            ];
        });
    }
}