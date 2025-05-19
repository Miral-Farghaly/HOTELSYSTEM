<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Admin - Booking Management",
 *     description="API Endpoints for managing bookings (Admin only)"
 * )
 */
class BookingController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/admin/bookings",
     *     summary="Get all bookings",
     *     description="Returns a paginated list of all bookings with user and room details",
     *     tags={"Admin - Booking Management"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="user_id", type="integer"),
     *                     @OA\Property(property="room_id", type="integer"),
     *                     @OA\Property(property="check_in_date", type="string", format="date"),
     *                     @OA\Property(property="check_out_date", type="string", format="date"),
     *                     @OA\Property(property="status", type="string"),
     *                     @OA\Property(property="total_amount", type="number", format="float"),
     *                     @OA\Property(
     *                         property="user",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="name", type="string"),
     *                         @OA\Property(property="email", type="string")
     *                     ),
     *                     @OA\Property(
     *                         property="room",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="room_number", type="string"),
     *                         @OA\Property(property="type", type="string")
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="total", type="integer"),
     *                 @OA\Property(property="per_page", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Unauthorized - User is not an admin")
     * )
     */
    public function index()
    {
        $bookings = Booking::with(['user:id,name,email', 'room:id,room_number,type'])
            ->latest()
            ->paginate();

        return response()->json([
            'data' => $bookings
        ]);
    }
} 