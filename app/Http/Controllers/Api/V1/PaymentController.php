<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\PaymentResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * @OA\Tag(
 *     name="Payments",
 *     description="API Endpoints for payment processing"
 * )
 */
class PaymentController extends Controller
{
    private $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/payments/create-intent",
     *     summary="Create a payment intent",
     *     tags={"Payments"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"booking_id"},
     *             @OA\Property(property="booking_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment intent created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="clientSecret", type="string"),
     *             @OA\Property(property="paymentIntentId", type="string")
     *         )
     *     )
     * )
     */
    public function createIntent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id'
        ]);

        $booking = Booking::findOrFail($validated['booking_id']);
        
        try {
            $intent = $this->paymentService->createPaymentIntent($booking);
            return response()->json($intent);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/payments/confirm",
     *     summary="Confirm a payment",
     *     tags={"Payments"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"payment_intent_id"},
     *             @OA\Property(property="payment_intent_id", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment confirmed successfully"
     *     )
     * )
     */
    public function confirm(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'payment_intent_id' => 'required|string'
        ]);

        try {
            $success = $this->paymentService->confirmPayment($validated['payment_intent_id']);
            return response()->json(['success' => $success]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/payments/{payment}/refund",
     *     summary="Refund a payment",
     *     tags={"Payments"},
     *     @OA\Parameter(
     *         name="payment",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="amount", type="number"),
     *             @OA\Property(property="reason", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment refunded successfully"
     *     )
     * )
     */
    public function refund(Request $request, Payment $payment): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'nullable|numeric|min:0',
            'reason' => 'required|string'
        ]);

        try {
            $success = $this->paymentService->refundPayment(
                $payment->transaction_id,
                $validated['amount'] ?? null
            );

            if ($success) {
                $payment->update([
                    'status' => 'refunded',
                    'refund_reason' => $validated['reason']
                ]);
            }

            return response()->json(['success' => $success]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function processPayment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'booking_id' => 'required|exists:bookings,id',
            'payment_method' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Generate unique transaction ID
        $transactionId = Str::uuid()->toString();

        // Create payment record
        $payment = Payment::create([
            'transaction_id' => $transactionId,
            'amount' => $request->amount,
            'currency' => strtoupper($request->currency),
            'booking_id' => $request->booking_id,
            'payment_method' => $request->payment_method,
            'status' => 'pending'
        ]);

        // Process payment through payment gateway
        try {
            // Here we would integrate with a real payment gateway
            // For now, we'll simulate a successful payment
            $payment->update(['status' => 'completed']);

            // Send confirmation email
            // event(new PaymentProcessed($payment));

            return response()->json([
                'status' => 'success',
                'data' => [
                    'transaction_id' => $transactionId,
                    'status' => 'completed'
                ]
            ]);
        } catch (\Exception $e) {
            $payment->update(['status' => 'failed']);
            return response()->json([
                'status' => 'error',
                'message' => 'Payment processing failed'
            ], 500);
        }
    }

    public function getStatus(string $transactionId): JsonResponse
    {
        $payment = Payment::where('transaction_id', $transactionId)->first();

        if (!$payment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => new PaymentResource($payment)
        ]);
    }
} 