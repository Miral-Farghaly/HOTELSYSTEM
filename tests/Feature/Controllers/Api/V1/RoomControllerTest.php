<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\Room;
use App\Models\User;
use App\Models\RoomType;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class RoomControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $admin;
    protected RoomType $roomType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->admin->givePermissionTo('manage-rooms');

        $this->roomType = RoomType::factory()->create();
    }

    public function test_can_list_rooms(): void
    {
        Room::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/rooms');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'number',
                        'floor',
                        'status',
                        'description',
                        'amenities',
                        'is_maintenance',
                        'created_at',
                        'updated_at',
                    ]
                ]
            ]);
    }

    public function test_can_create_room(): void
    {
        Sanctum::actingAs($this->admin);

        $roomData = [
            'number' => '101',
            'room_type_id' => $this->roomType->id,
            'floor' => 1,
            'status' => 'active',
            'description' => 'Test room',
            'amenities' => ['wifi', 'tv'],
        ];

        $response = $this->postJson('/api/v1/rooms', $roomData);

        $response->assertCreated()
            ->assertJsonFragment([
                'number' => '101',
                'floor' => 1,
                'status' => 'active',
            ]);

        $this->assertDatabaseHas('rooms', [
            'number' => '101',
            'room_type_id' => $this->roomType->id,
        ]);
    }

    public function test_can_show_room(): void
    {
        $room = Room::factory()->create();

        $response = $this->getJson("/api/v1/rooms/{$room->id}");

        $response->assertOk()
            ->assertJsonFragment([
                'id' => $room->id,
                'number' => $room->number,
            ]);
    }

    public function test_can_update_room(): void
    {
        Sanctum::actingAs($this->admin);

        $room = Room::factory()->create();
        $updateData = [
            'number' => '102',
            'room_type_id' => $this->roomType->id,
            'floor' => 2,
            'description' => 'Updated description',
        ];

        $response = $this->putJson("/api/v1/rooms/{$room->id}", $updateData);

        $response->assertOk()
            ->assertJsonFragment([
                'number' => '102',
                'floor' => 2,
            ]);

        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'number' => '102',
        ]);
    }

    public function test_can_delete_room(): void
    {
        Sanctum::actingAs($this->admin);

        $room = Room::factory()->create();

        $response = $this->deleteJson("/api/v1/rooms/{$room->id}");

        $response->assertNoContent();
        $this->assertSoftDeleted('rooms', ['id' => $room->id]);
    }

    public function test_can_check_room_availability(): void
    {
        $room = Room::factory()->create();

        $response = $this->getJson('/api/v1/rooms/check-availability?' . http_build_query([
            'check_in' => now()->addDay()->toDateString(),
            'check_out' => now()->addDays(2)->toDateString(),
            'room_type_id' => $room->room_type_id,
        ]));

        $response->assertOk()
            ->assertJsonStructure([
                'available',
                'total_rooms',
                'rooms',
                'check_in',
                'check_out',
            ]);
    }

    public function test_can_toggle_room_maintenance(): void
    {
        Sanctum::actingAs($this->admin);

        $room = Room::factory()->create(['is_maintenance' => false]);

        $response = $this->putJson("/api/v1/rooms/{$room->id}/maintenance");

        $response->assertOk()
            ->assertJsonFragment([
                'is_maintenance' => true,
            ]);

        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'is_maintenance' => true,
        ]);
    }

    public function test_unauthorized_user_cannot_manage_rooms(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/rooms', [
            'number' => '101',
            'room_type_id' => $this->roomType->id,
            'floor' => 1,
        ]);

        $response->assertForbidden();
    }
} 