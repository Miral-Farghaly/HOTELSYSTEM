<?php

namespace Tests\Feature\Controllers\Api;

use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
        
        $this->user = User::factory()->create();
        $this->user->assignRole('customer');
    }

    public function test_list_rooms()
    {
        Room::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/rooms');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_create_room()
    {
        $roomData = [
            'number' => '101',
            'type' => 'deluxe',
            'floor' => 1,
            'description' => 'Luxury room with ocean view',
            'price_per_night' => 200.00,
            'base_price' => 200.00,
            'currency' => 'USD',
            'capacity' => 2,
            'amenities' => ['wifi', 'minibar', 'tv']
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/rooms', $roomData);

        $response->assertStatus(201)
            ->assertJsonFragment(['number' => '101']);
    }

    public function test_update_room()
    {
        $room = Room::factory()->create();
        $updateData = ['price_per_night' => 250.00, 'base_price' => 250.00];

        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/rooms/{$room->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment(['price_per_night' => 250.00]);
    }

    public function test_delete_room()
    {
        $room = Room::factory()->create();

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/v1/rooms/{$room->id}");

        $response->assertStatus(204);
        $this->assertSoftDeleted($room);
    }

    public function test_unauthorized_user_cannot_create_room()
    {
        $roomData = [
            'number' => '102',
            'type' => 'standard',
            'floor' => 1,
            'price_per_night' => 150.00,
            'base_price' => 150.00
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/rooms', $roomData);

        $response->assertStatus(403);
    }

    public function test_check_room_availability()
    {
        $room = Room::factory()->available()->create();

        $response = $this->getJson("/api/v1/rooms/{$room->id}/availability");

        $response->assertStatus(200)
            ->assertJson(['available' => true]);
    }

    public function test_list_available_rooms()
    {
        Room::factory()->available()->count(2)->create();
        Room::factory()->unavailable()->create();

        $response = $this->getJson('/api/v1/rooms/available');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_mark_room_for_maintenance()
    {
        $room = Room::factory()->available()->create();

        $response = $this->actingAs($this->admin)
            ->postJson("/api/v1/rooms/{$room->id}/maintenance", [
                'needs_maintenance' => true,
                'maintenance_notes' => 'Plumbing issues'
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'is_available' => false,
                'needs_maintenance' => true
            ]);
    }
} 