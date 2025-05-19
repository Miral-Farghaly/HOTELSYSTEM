<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::with(['type', 'amenities'])
            ->latest()
            ->paginate();

        return response()->json([
            'data' => $rooms
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'room_number' => 'required|string|unique:rooms',
            'type' => 'required|string',
            'price_per_night' => 'required|numeric|min:0',
            'capacity' => 'required|integer|min:1',
            'description' => 'required|string',
            'amenities' => 'required|array'
        ]);

        $room = Room::create([
            'room_number' => $request->room_number,
            'type' => $request->type,
            'price_per_night' => $request->price_per_night,
            'capacity' => $request->capacity,
            'description' => $request->description,
            'amenities' => $request->amenities,
            'is_available' => true
        ]);

        return response()->json([
            'data' => $room
        ], 201);
    }

    public function update(Request $request, Room $room)
    {
        $request->validate([
            'price_per_night' => 'sometimes|numeric|min:0',
            'description' => 'sometimes|string',
            'amenities' => 'sometimes|array'
        ]);

        $room->update($request->all());

        return response()->json([
            'data' => $room
        ]);
    }

    public function toggleMaintenance(Request $request, Room $room)
    {
        $request->validate([
            'status' => 'required|in:under_maintenance,available',
            'notes' => 'required|string'
        ]);

        $room->update([
            'is_available' => $request->status === 'available',
            'maintenance_status' => $request->status,
            'maintenance_notes' => $request->notes
        ]);

        return response()->json([
            'data' => $room
        ]);
    }
} 