<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RoomController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show', 'available', 'checkAvailability']);
        $this->middleware('role:admin')->only(['store', 'update', 'destroy', 'maintenance']);
    }

    public function index(): JsonResponse
    {
        $rooms = Room::all();
        return response()->json(['data' => $rooms]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'number' => 'required|string|unique:rooms',
            'type' => 'required|string',
            'floor' => 'required|integer',
            'description' => 'nullable|string',
            'price_per_night' => 'required|numeric|min:0',
            'base_price' => 'required|numeric|min:0',
            'capacity' => 'required|integer|min:1',
            'amenities' => 'nullable|array'
        ]);

        $room = Room::create($validated);
        return response()->json($room, 201);
    }

    public function show(Room $room): JsonResponse
    {
        return response()->json($room);
    }

    public function update(Request $request, Room $room): JsonResponse
    {
        $validated = $request->validate([
            'number' => 'string|unique:rooms,number,'.$room->id,
            'type' => 'string',
            'floor' => 'integer',
            'description' => 'nullable|string',
            'price_per_night' => 'numeric|min:0',
            'base_price' => 'numeric|min:0',
            'capacity' => 'integer|min:1',
            'amenities' => 'nullable|array'
        ]);

        $room->update($validated);
        return response()->json($room);
    }

    public function destroy(Room $room): JsonResponse
    {
        $room->delete();
        return response()->json(null, 204);
    }

    public function checkAvailability(Room $room): JsonResponse
    {
        return response()->json([
            'available' => $room->is_available && !$room->bookings()
                ->where('status', 'confirmed')
                ->where(function ($query) {
                    $query->where('check_in_date', '<=', now())
                        ->where('check_out_date', '>=', now());
                })->exists()
        ]);
    }

    public function available(): JsonResponse
    {
        $rooms = Room::where('is_available', true)->get();
        return response()->json(['data' => $rooms]);
    }

    public function maintenance(Request $request, Room $room): JsonResponse
    {
        $validated = $request->validate([
            'needs_maintenance' => 'required|boolean',
            'maintenance_notes' => 'nullable|string'
        ]);

        $room->update([
            'is_available' => !$validated['needs_maintenance'],
            'needs_maintenance' => $validated['needs_maintenance']
        ]);

        return response()->json($room);
    }
} 