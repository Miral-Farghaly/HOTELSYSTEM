<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Room;
use App\Models\Booking;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $user;
    private $room;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin user
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
        
        // Create regular user
        $this->user = User::factory()->create();
        
        // Create test room
        $this->room = Room::factory()->create();
    }

    public function test_admin_can_view_all_users()
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/admin/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'created_at'
                    ]
                ]
            ]);
    }

    public function test_admin_can_create_room()
    {
        $roomData = [
            'room_number' => '201',
            'type' => 'Suite',
            'price_per_night' => 200.00,
            'capacity' => 2,
            'description' => 'Luxury suite with ocean view',
            'amenities' => ['wifi', 'minibar', 'balcony']
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/admin/rooms', $roomData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'room_number',
                    'type',
                    'price_per_night',
                    'is_available'
                ]
            ]);

        $this->assertDatabaseHas('rooms', [
            'room_number' => '201',
            'type' => 'Suite'
        ]);
    }

    public function test_admin_can_update_room()
    {
        $updateData = [
            'price_per_night' => 250.00,
            'description' => 'Updated room description'
        ];

        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/admin/rooms/{$this->room->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'price_per_night' => 250.00,
                    'description' => 'Updated room description'
                ]
            ]);
    }

    public function test_admin_can_view_all_bookings()
    {
        Booking::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/admin/bookings');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'room_id',
                        'check_in_date',
                        'check_out_date',
                        'status'
                    ]
                ]
            ]);
    }

    public function test_admin_can_manage_user_roles()
    {
        $response = $this->actingAs($this->admin)
            ->postJson("/api/v1/admin/users/{$this->user->id}/roles", [
                'role' => 'manager'
            ]);

        $response->assertStatus(200);
        $this->assertTrue($this->user->fresh()->hasRole('manager'));
    }

    public function test_non_admin_cannot_access_admin_routes()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/admin/users');

        $response->assertStatus(403);
    }

    public function test_admin_can_view_booking_statistics()
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/admin/statistics/bookings');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_bookings',
                'total_revenue',
                'occupancy_rate',
                'popular_rooms'
            ]);
    }

    public function test_admin_can_manage_room_maintenance()
    {
        $response = $this->actingAs($this->admin)
            ->postJson("/api/v1/admin/rooms/{$this->room->id}/maintenance", [
                'status' => 'under_maintenance',
                'notes' => 'Annual maintenance check'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'is_available' => false,
                    'maintenance_status' => 'under_maintenance'
                ]
            ]);
    }
} 