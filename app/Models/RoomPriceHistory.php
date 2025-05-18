<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @OA\Schema(
 *     schema="RoomPriceHistory",
 *     required={"room_id", "old_price", "new_price"},
 *     @OA\Property(property="id", type="integer", format="int64", readOnly=true),
 *     @OA\Property(property="room_id", type="integer", format="int64"),
 *     @OA\Property(property="old_price", type="number", format="float"),
 *     @OA\Property(property="new_price", type="number", format="float"),
 *     @OA\Property(property="reason", type="string", nullable=true),
 *     @OA\Property(property="changed_by", type="integer", format="int64"),
 *     @OA\Property(property="created_at", type="string", format="date-time", readOnly=true),
 *     @OA\Property(property="updated_at", type="string", format="date-time", readOnly=true)
 * )
 */
class RoomPriceHistory extends Model
{
    protected $fillable = [
        'room_id',
        'old_price',
        'new_price',
        'reason',
        'changed_by',
    ];

    protected $casts = [
        'old_price' => 'decimal:2',
        'new_price' => 'decimal:2',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
} 