<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Booking;
use App\Models\Payment;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $booking;

    protected function setUp(): void
    {
        parent::setUp();
        
        Stripe::setApiKey(config('services.stripe.secret'));
        
        $this->user = User::factory()->create();
        $this->booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'total_price' => 300.00,
            'status' => 'confirmed'
        ]);
    }

    public function test_can_create_payment_intent()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/payments/create-intent', [
                'booking_id' => $this->booking->id,
                'currency' => 'usd'
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'client_secret',
                'payment_intent_id'
            ]);

        // Verify payment intent was created in Stripe
        $paymentIntentId = $response->json('payment_intent_id');
        $this->assertNotNull($paymentIntentId);
    }

    public function test_can_confirm_payment()
    {
        // Create a test payment
        $payment = Payment::factory()->create([
            'booking_id' => $this->booking->id,
            'amount' => 300.00,
            'status' => 'pending',
            'payment_intent_id' => 'pi_test_123'
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/payments/confirm', [
                'payment_intent_id' => $payment->payment_intent_id
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success'
            ]);

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'completed'
        ]);
    }

    public function test_can_process_refund()
    {
        $payment = Payment::factory()->create([
            'booking_id' => $this->booking->id,
            'amount' => 300.00,
            'status' => 'completed',
            'payment_intent_id' => 'pi_test_123'
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/payments/{$payment->id}/refund", [
                'reason' => 'customer_requested'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'refunded'
            ]);

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'refunded'
        ]);
    }

    public function test_cannot_process_invalid_payment()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/payments/confirm', [
                'payment_intent_id' => 'invalid_id'
            ]);

        $response->assertStatus(422);
    }

    public function test_payment_webhook_handling()
    {
        $webhookPayload = [
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id' => 'pi_test_123',
                    'amount' => 30000,
                    'status' => 'succeeded'
                ]
            ]
        ];

        $response = $this->postJson('/api/v1/payments/webhook', $webhookPayload, [
            'Stripe-Signature' => 'test_signature'
        ]);

        $response->assertStatus(200);
    }
} 