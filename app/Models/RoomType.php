<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *     schema="RoomType",
 *     required={"name", "description", "base_price"},
 *     @OA\Property(property="id", type="integer", format="int64", readOnly=true),
 *     @OA\Property(property="name", type="string", maxLength=255),
 *     @OA\Property(property="description", type="string"),
 *     @OA\Property(property="base_price", type="number", format="float"),
 *     @OA\Property(property="capacity", type="integer"),
 *     @OA\Property(property="amenities", type="array", @OA\Items(type="string")),
 *     @OA\Property(property="created_at", type="string", format="date-time", readOnly=true),
 *     @OA\Property(property="updated_at", type="string", format="date-time", readOnly=true),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
 * )
 */
class RoomType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'base_price',
        'capacity',
        'amenities',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'capacity' => 'integer',
        'amenities' => 'array',
    ];

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class, 'type_id');
    }

    public function getAvailableRooms(): HasMany
    {
        return $this->rooms()
            ->where('status', 'active')
            ->where('is_maintenance', false);
    }

    public function scopeActive($query)
    {
        return $query->whereHas('rooms', function ($query) {
            $query->where('status', 'active')
                ->where('is_maintenance', false);
        });
    }
} 