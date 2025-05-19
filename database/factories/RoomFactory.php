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
        return [
            'number' => $this->faker->unique()->numberBetween(100, 999),
            'type' => $this->faker->randomElement(['standard', 'deluxe', 'suite', 'presidential']),
            'floor' => $this->faker->numberBetween(1, 10),
            'description' => $this->faker->paragraph(),
            'price_per_night' => $price,
            'base_price' => $price,
            'currency' => 'USD',
            'capacity' => $this->faker->numberBetween(1, 6),
            'is_available' => $this->faker->boolean(80),
            'needs_maintenance' => $this->faker->boolean(10),
            'amenities' => $this->faker->randomElements(
                ['wifi', 'tv', 'minibar', 'safe', 'balcony', 'ocean_view', 'bathtub', 'kitchen'],
                $this->faker->numberBetween(3, 6)
            ),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function available()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_available' => true,
                'needs_maintenance' => false,
            ];
        });
    }

    public function unavailable()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_available' => false,
            ];
        });
    }

    public function needsMaintenance()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_available' => false,
                'needs_maintenance' => true,
            ];
        });
    }
}