<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(
 *     name="Admin - Statistics",
 *     description="API Endpoints for viewing hotel statistics (Admin only)"
 * )
 */
class StatisticsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/admin/statistics/bookings",
     *     summary="Get booking statistics",
     *     description="Returns various statistics about bookings, revenue, and room occupancy",
     *     tags={"Admin - Statistics"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="total_bookings", type="integer", example=150),
     *             @OA\Property(property="total_revenue", type="number", format="float", example=25000.50),
     *             @OA\Property(property="occupancy_rate", type="number", format="float", example=75.5),
     *             @OA\Property(
     *                 property="popular_rooms",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="room_number", type="string"),
     *                     @OA\Property(property="type", type="string"),
     *                     @OA\Property(property="bookings_count", type="integer")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Unauthorized - User is not an admin")
     * )
     */
    public function bookings()
    {
        $totalBookings = Booking::count();
        $totalRevenue = Booking::sum('total_amount');
        
        $occupiedRooms = Room::whereHas('bookings', function ($query) {
            $query->where('status', 'active')
                ->where('check_in_date', '<=', now())
                ->where('check_out_date', '>=', now());
        })->count();
        
        $totalRooms = Room::count();
        $occupancyRate = $totalRooms > 0 ? ($occupiedRooms / $totalRooms) * 100 : 0;

        $popularRooms = Room::withCount('bookings')
            ->orderBy('bookings_count', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'total_bookings' => $totalBookings,
            'total_revenue' => $totalRevenue,
            'occupancy_rate' => round($occupancyRate, 2),
            'popular_rooms' => $popularRooms
        ]);
    }
} 