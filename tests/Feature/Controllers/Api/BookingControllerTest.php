<?php

namespace Tests\Feature\Controllers\Api;

use App\Models\Room;
use App\Models\User;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private Room $room;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->customer = User::factory()->create();
        $this->customer->assignRole('customer');
        
        $this->room = Room::factory()->available()->create([
            'price_per_night' => 200.00,
            'base_price' => 200.00
        ]);
    }

    public function test_create_booking()
    {
        $bookingData = [
            'room_id' => $this->room->id,
            'check_in_date' => now()->addDays(1)->format('Y-m-d'),
            'check_out_date' => now()->addDays(3)->format('Y-m-d'),
            'guests_count' => 2,
            'special_requests' => 'Early check-in requested'
        ];

        $response = $this->actingAs($this->customer)
            ->postJson('/api/v1/bookings', $bookingData);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'room_id' => $this->room->id,
                'status' => 'pending'
            ]);
    }

    public function test_get_user_bookings()
    {
        Booking::factory()->count(3)->create([
            'user_id' => $this->customer->id,
            'room_id' => $this->room->id
        ]);

        $response = $this->actingAs($this->customer)
            ->getJson('/api/v1/bookings');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_cancel_booking()
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->customer->id,
            'room_id' => $this->room->id,
            'status' => 'confirmed'
        ]);

        $response = $this->actingAs($this->customer)
            ->putJson("/api/v1/bookings/{$booking->id}/cancel");

        $response->assertStatus(200)
            ->assertJsonFragment(['status' => 'cancelled']);
    }

    public function test_cannot_book_unavailable_room()
    {
        $unavailableRoom = Room::factory()->unavailable()->create();

        $bookingData = [
            'room_id' => $unavailableRoom->id,
            'check_in_date' => now()->addDays(1)->format('Y-m-d'),
            'check_out_date' => now()->addDays(3)->format('Y-m-d'),
            'guests_count' => 2
        ];

        $response = $this->actingAs($this->customer)
            ->postJson('/api/v1/bookings', $bookingData);

        $response->assertStatus(422);
    }

    public function test_cannot_book_overlapping_dates()
    {
        // Create an existing booking
        Booking::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $this->customer->id,
            'check_in_date' => now()->addDays(2),
            'check_out_date' => now()->addDays(4),
            'status' => 'confirmed'
        ]);

        $bookingData = [
            'room_id' => $this->room->id,
            'check_in_date' => now()->addDays(3)->format('Y-m-d'),
            'check_out_date' => now()->addDays(5)->format('Y-m-d'),
            'guests_count' => 2
        ];

        $response = $this->actingAs($this->customer)
            ->postJson('/api/v1/bookings', $bookingData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['check_in_date']);
    }
} 