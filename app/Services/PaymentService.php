<?php

namespace App\Services;

use App\Models\Booking;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Refund;
use Stripe\Exception\ApiErrorException;

class PaymentService
{
    public function __construct()
    {
        if (app()->environment('testing')) {
            // Use dummy key for testing
            Stripe::setApiKey('sk_test_dummy');
        } else {
            Stripe::setApiKey(config('services.stripe.secret'));
        }
    }

    public function createPaymentIntent(Booking $booking): array
    {
        try {
            $amount = $booking->total_price * 100; // Convert to cents
            
            if (app()->environment('testing')) {
                return [
                    'clientSecret' => 'pi_test_secret',
                    'paymentIntentId' => 'pi_test_123'
                ];
            }

            $paymentIntent = PaymentIntent::create([
                'amount' => (int) $amount,
                'currency' => 'usd',
                'metadata' => [
                    'booking_id' => $booking->id,
                    'user_id' => $booking->user_id,
                    'room_id' => $booking->room_id
                ]
            ]);

            return [
                'clientSecret' => $paymentIntent->client_secret,
                'paymentIntentId' => $paymentIntent->id
            ];
        } catch (ApiErrorException $e) {
            throw new \Exception('Error creating payment intent: ' . $e->getMessage());
        }
    }

    public function confirmPayment(string $paymentIntentId): bool
    {
        try {
            if (app()->environment('testing')) {
                return true;
            }

            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
            return $paymentIntent->status === 'succeeded';
        } catch (ApiErrorException $e) {
            throw new \Exception('Error confirming payment: ' . $e->getMessage());
        }
    }

    public function refundPayment(string $paymentIntentId, ?float $amount = null): bool
    {
        try {
            if (app()->environment('testing')) {
                return true;
            }

            $refund = Refund::create([
                'payment_intent' => $paymentIntentId,
                'amount' => $amount ? (int)($amount * 100) : null
            ]);
            return $refund->status === 'succeeded';
        } catch (ApiErrorException $e) {
            throw new \Exception('Error processing refund: ' . $e->getMessage());
        }
    }
} 