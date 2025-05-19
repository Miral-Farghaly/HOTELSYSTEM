<?php

namespace Tests\Feature\Services;

use Tests\TestCase;
use App\Models\User;
use App\Models\Room;
use App\Models\Booking;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Stripe\PaymentIntent;
use Stripe\Exception\ApiErrorException;
use Mockery;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    private PaymentService $paymentService;
    private User $user;
    private Room $room;
    private Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();

        $this->paymentService = new PaymentService();
        
        // Create test data
        $this->user = User::factory()->create();
        $this->room = Room::factory()->create([
            'price_per_night' => 100.00
        ]);
        
        $this->booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'room_id' => $this->room->id,
            'total_price' => 300.00,
            'status' => 'pending'
        ]);
    }

    public function test_creates_payment_intent()
    {
        $result = $this->paymentService->createPaymentIntent($this->booking);

        $this->assertArrayHasKey('clientSecret', $result);
        $this->assertArrayHasKey('paymentIntentId', $result);
        
        // Verify the amount is correct (in cents)
        $this->assertEquals(30000, $result['amount']);
    }

    public function test_confirms_successful_payment()
    {
        $paymentIntentId = 'pi_test_123';
        
        $result = $this->paymentService->confirmPayment($paymentIntentId);
        
        $this->assertTrue($result);
        $this->assertEquals('confirmed', $this->booking->fresh()->status);
    }

    public function test_handles_failed_payment()
    {
        $this->expectException(\Exception::class);
        
        $paymentIntentId = 'pi_test_failed';
        
        $this->paymentService->confirmPayment($paymentIntentId);
        
        $this->assertEquals('pending', $this->booking->fresh()->status);
    }

    public function test_processes_refund()
    {
        $paymentIntentId = 'pi_test_123';
        
        $result = $this->paymentService->refundPayment($paymentIntentId);
        
        $this->assertTrue($result);
    }

    public function test_handles_partial_refund()
    {
        $paymentIntentId = 'pi_test_123';
        $amount = 100.00;
        
        $result = $this->paymentService->refundPayment($paymentIntentId, $amount);
        
        $this->assertTrue($result);
    }

    public function test_handles_refund_error()
    {
        $this->expectException(\Exception::class);
        
        $paymentIntentId = 'pi_test_invalid';
        
        $this->paymentService->refundPayment($paymentIntentId);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
} 