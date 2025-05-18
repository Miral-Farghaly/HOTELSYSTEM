<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Room",
 *     required={"name", "type", "price", "capacity"},
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="name", type="string", example="Deluxe Ocean View"),
 *     @OA\Property(property="type", type="string", example="deluxe"),
 *     @OA\Property(property="description", type="string", example="Spacious room with ocean view"),
 *     @OA\Property(property="price", type="number", format="float", example=299.99),
 *     @OA\Property(property="capacity", type="integer", example=2),
 *     @OA\Property(property="amenities", type="array", @OA\Items(type="string")),
 *     @OA\Property(property="image_url", type="string", format="url"),
 *     @OA\Property(property="created_at", type="string", format="datetime"),
 *     @OA\Property(property="updated_at", type="string", format="datetime")
 * )
 */
class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'description',
        'price',
        'capacity',
        'amenities',
        'image_url'
    ];

    protected $casts = [
        'amenities' => 'array',
        'price' => 'float'
    ];

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function isAvailable($checkIn, $checkOut)
    {
        return !$this->reservations()
            ->where(function ($query) use ($checkIn, $checkOut) {
                $query->whereBetween('check_in', [$checkIn, $checkOut])
                    ->orWhereBetween('check_out', [$checkIn, $checkOut])
                    ->orWhere(function ($query) use ($checkIn, $checkOut) {
                        $query->where('check_in', '<=', $checkIn)
                            ->where('check_out', '>=', $checkOut);
                    });
            })
            ->exists();
    }
} 