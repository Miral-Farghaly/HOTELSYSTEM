<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Room;
use App\Models\Booking;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $room;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user
        $this->user = User::factory()->create();
        
        // Create test room
        $this->room = Room::factory()->create([
            'room_number' => '101',
            'type' => 'Deluxe',
            'price_per_night' => 150.00,
            'is_available' => true
        ]);
    }

    public function test_user_can_view_available_rooms()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/rooms/available');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'room_number',
                        'type',
                        'price_per_night',
                        'is_available'
                    ]
                ]
            ]);
    }

    public function test_user_can_create_booking()
    {
        $bookingData = [
            'room_id' => $this->room->id,
            'check_in_date' => now()->addDays(1)->format('Y-m-d'),
            'check_out_date' => now()->addDays(3)->format('Y-m-d'),
            'guests_count' => 2,
            'special_requests' => 'Extra pillows'
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/bookings', $bookingData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'room_id',
                    'user_id',
                    'check_in_date',
                    'check_out_date',
                    'total_price',
                    'status',
                    'created_at'
                ]
            ]);

        $this->assertDatabaseHas('bookings', [
            'room_id' => $this->room->id,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_user_cannot_book_unavailable_room()
    {
        // Make room unavailable
        $this->room->update(['is_available' => false]);

        $bookingData = [
            'room_id' => $this->room->id,
            'check_in_date' => now()->addDays(1)->format('Y-m-d'),
            'check_out_date' => now()->addDays(3)->format('Y-m-d'),
            'guests_count' => 2
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/bookings', $bookingData);

        $response->assertStatus(422);
    }

    public function test_user_can_cancel_booking()
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'room_id' => $this->room->id,
            'status' => 'confirmed'
        ]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/bookings/{$booking->id}/cancel");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'status' => 'cancelled'
                ]
            ]);
    }

    public function test_user_cannot_book_with_invalid_dates()
    {
        $bookingData = [
            'room_id' => $this->room->id,
            'check_in_date' => now()->subDays(1)->format('Y-m-d'), // Past date
            'check_out_date' => now()->format('Y-m-d'),
            'guests_count' => 2
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/bookings', $bookingData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['check_in_date']);
    }
} 