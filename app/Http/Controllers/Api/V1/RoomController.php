<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Services\RoomService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Resources\RoomResource;
use App\Http\Requests\RoomRequest;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Rooms",
 *     description="API Endpoints for room management"
 * )
 */
class RoomController extends Controller
{
    protected $roomService;

    public function __construct(RoomService $roomService)
    {
        $this->roomService = $roomService;
        $this->middleware('auth:sanctum');
        $this->middleware('permission:manage-rooms')->except(['index', 'show', 'checkAvailability']);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/rooms",
     *     summary="List all rooms",
     *     tags={"Rooms"},
     *     @OA\Parameter(
     *         name="type_id",
     *         in="query",
     *         description="Filter by room type ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by room status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"active", "inactive", "maintenance"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of rooms",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Room")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $rooms = $this->roomService->getAllRooms($request->all());
        return response()->json(RoomResource::collection($rooms));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/rooms",
     *     summary="Create a new room",
     *     tags={"Rooms"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/RoomRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Room created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Room")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(RoomRequest $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'required|string',
            'available' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $room = $this->roomService->createRoom($request->validated());
        return response()->json(new RoomResource($room), 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/rooms/{room}",
     *     summary="Get room details",
     *     tags={"Rooms"},
     *     @OA\Parameter(
     *         name="room",
     *         in="path",
     *         description="Room ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Room details",
     *         @OA\JsonContent(ref="#/components/schemas/Room")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Room not found"
     *     )
     * )
     */
    public function show(Room $room): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => new RoomResource($room)
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/rooms/{room}",
     *     summary="Update room details",
     *     tags={"Rooms"},
     *     @OA\Parameter(
     *         name="room",
     *         in="path",
     *         description="Room ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/RoomRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Room updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Room")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Room not found"
     *     )
     * )
     */
    public function update(RoomRequest $request, Room $room): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'string|max:255',
            'price' => 'numeric|min:0',
            'description' => 'string',
            'available' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $room = $this->roomService->updateRoom($room, $request->validated());
        return response()->json(new RoomResource($room));
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/rooms/{room}",
     *     summary="Delete a room",
     *     tags={"Rooms"},
     *     @OA\Parameter(
     *         name="room",
     *         in="path",
     *         description="Room ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Room deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Room not found"
     *     )
     * )
     */
    public function destroy(Room $room): JsonResponse
    {
        $this->roomService->deleteRoom($room);
        return response()->json([
            'status' => 'success',
            'message' => 'Room deleted successfully'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/rooms/check-availability",
     *     summary="Check room availability",
     *     tags={"Rooms"},
     *     @OA\Parameter(
     *         name="check_in",
     *         in="query",
     *         description="Check-in date (Y-m-d)",
     *         required=true,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="check_out",
     *         in="query",
     *         description="Check-out date (Y-m-d)",
     *         required=true,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="room_type_id",
     *         in="query",
     *         description="Room type ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Room availability status"
     *     )
     * )
     */
    public function checkAvailability(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'check_in' => 'required|date|after:today',
            'check_out' => 'required|date|after:check_in'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $availability = $this->roomService->checkAvailability(
            $request->input('check_in'),
            $request->input('check_out'),
            $request->input('room_type_id')
        );
        return response()->json($availability);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/rooms/{room}/maintenance",
     *     summary="Toggle room maintenance status",
     *     tags={"Rooms"},
     *     @OA\Parameter(
     *         name="room",
     *         in="path",
     *         description="Room ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Room maintenance status updated",
     *         @OA\JsonContent(ref="#/components/schemas/Room")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Room not found"
     *     )
     * )
     */
    public function maintenance(Room $room): JsonResponse
    {
        $room = $this->roomService->toggleMaintenance($room);
        return response()->json(new RoomResource($room));
    }
} 