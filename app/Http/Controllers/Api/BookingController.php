<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(): JsonResponse
    {
        $bookings = Auth::user()->bookings()->with('room')->get();
        return response()->json(['data' => $bookings]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'check_in_date' => 'required|date|after:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'guests_count' => 'required|integer|min:1',
            'special_requests' => 'nullable|string'
        ]);

        $room = Room::findOrFail($validated['room_id']);

        // Check if room is available
        if (!$room->is_available) {
            return response()->json(['message' => 'Room is not available'], 422);
        }

        // Check for overlapping bookings
        $hasOverlap = $room->bookings()
            ->where('status', 'confirmed')
            ->where(function ($query) use ($validated) {
                $query->whereBetween('check_in_date', [$validated['check_in_date'], $validated['check_out_date']])
                    ->orWhereBetween('check_out_date', [$validated['check_in_date'], $validated['check_out_date']]);
            })->exists();

        if ($hasOverlap) {
            return response()->json([
                'message' => 'Room is already booked for these dates',
                'errors' => ['check_in_date' => ['The selected dates are not available']]
            ], 422);
        }

        // Calculate total price
        $checkIn = Carbon::parse($validated['check_in_date']);
        $checkOut = Carbon::parse($validated['check_out_date']);
        $days = $checkIn->diffInDays($checkOut);
        $totalPrice = $room->price_per_night * $days;

        $booking = Auth::user()->bookings()->create([
            'room_id' => $validated['room_id'],
            'check_in_date' => $validated['check_in_date'],
            'check_out_date' => $validated['check_out_date'],
            'guests_count' => $validated['guests_count'],
            'special_requests' => $validated['special_requests'] ?? null,
            'total_price' => $totalPrice,
            'status' => 'pending'
        ]);

        return response()->json($booking->load('room'), 201);
    }

    public function show(Booking $booking): JsonResponse
    {
        $this->authorize('view', $booking);
        return response()->json($booking->load('room'));
    }

    public function cancel(Booking $booking): JsonResponse
    {
        $this->authorize('update', $booking);

        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            return response()->json(['message' => 'Booking cannot be cancelled'], 422);
        }

        $booking->update(['status' => 'cancelled']);
        return response()->json($booking);
    }
} 