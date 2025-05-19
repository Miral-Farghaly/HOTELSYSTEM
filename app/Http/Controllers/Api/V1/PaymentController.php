<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\PaymentResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
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

    public function refund(Request $request, string $transactionId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $payment = Payment::where('transaction_id', $transactionId)
                        ->where('status', 'completed')
                        ->first();

        if (!$payment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment not found or cannot be refunded'
            ], 404);
        }

        try {
            // Here we would integrate with payment gateway for refund
            // For now, we'll simulate a successful refund
            $payment->update([
                'status' => 'refunded',
                'refund_reason' => $request->reason
            ]);

            // Send refund notification
            // event(new PaymentRefunded($payment));

            return response()->json([
                'status' => 'success',
                'message' => 'Payment refunded successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Refund processing failed'
            ], 500);
        }
    }
} 