<?php

namespace Database\Factories;

use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomFactory extends Factory
{
    protected $model = Room::class;

    public function definition(): array
    {
        return [
            'number' => $this->faker->unique()->numberBetween(100, 999),
            'room_type_id' => RoomType::factory(),
            'floor' => $this->faker->numberBetween(1, 10),
            'status' => $this->faker->randomElement(['active', 'inactive', 'maintenance']),
            'description' => $this->faker->paragraph(),
            'amenities' => $this->faker->randomElements([
                'wifi',
                'tv',
                'minibar',
                'safe',
                'air_conditioning',
                'desk',
                'bathtub',
                'shower',
                'hairdryer',
                'iron'
            ], $this->faker->numberBetween(3, 6)),
            'is_maintenance' => $this->faker->boolean(10),
        ];
    }

    public function active(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'active',
                'is_maintenance' => false,
            ];
        });
    }

    public function maintenance(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'maintenance',
                'is_maintenance' => true,
            ];
        });
    }

    public function inactive(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'inactive',
                'is_maintenance' => false,
            ];
        });
    }
} 