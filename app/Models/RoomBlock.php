<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

/**
 * @OA\Schema(
 *     schema="RoomBlock",
 *     required={"room_id", "reason", "blocked_by"},
 *     @OA\Property(property="id", type="integer", format="int64", readOnly=true),
 *     @OA\Property(property="room_id", type="integer", format="int64"),
 *     @OA\Property(property="reason", type="string"),
 *     @OA\Property(property="start_date", type="string", format="date"),
 *     @OA\Property(property="end_date", type="string", format="date", nullable=true),
 *     @OA\Property(property="notes", type="string", nullable=true),
 *     @OA\Property(property="priority", type="integer"),
 *     @OA\Property(property="blocked_by", type="integer", format="int64"),
 *     @OA\Property(property="created_at", type="string", format="date-time", readOnly=true),
 *     @OA\Property(property="updated_at", type="string", format="date-time", readOnly=true)
 * )
 */
class RoomBlock extends Model
{
    protected $fillable = [
        'room_id',
        'reason',
        'start_date',
        'end_date',
        'notes',
        'priority',
        'blocked_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'priority' => 'integer',
    ];

    protected $attributes = [
        'priority' => 1,
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function blockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_by');
    }

    public function isActive(): bool
    {
        $now = Carbon::now();
        
        if ($this->end_date) {
            return $now->between($this->start_date, $this->end_date);
        }
        
        return $now->gte($this->start_date);
    }

    public function scopeActive($query)
    {
        $now = Carbon::now();
        return $query->where(function ($q) use ($now) {
            $q->whereNull('end_date')
                ->where('start_date', '<=', $now)
                ->orWhere(function ($q) use ($now) {
                    $q->whereNotNull('end_date')
                        ->where('start_date', '<=', $now)
                        ->where('end_date', '>=', $now);
                });
        });
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now());
    }

    public function scopeHighPriority($query)
    {
        return $query->where('priority', '>=', 5);
    }

    public function scopeForDateRange($query, Carbon $start, Carbon $end)
    {
        return $query->where(function ($q) use ($start, $end) {
            $q->whereBetween('start_date', [$start, $end])
                ->orWhere(function ($q) use ($start, $end) {
                    $q->whereNotNull('end_date')
                        ->where(function ($q) use ($start, $end) {
                            $q->whereBetween('end_date', [$start, $end])
                                ->orWhere(function ($q) use ($start, $end) {
                                    $q->where('start_date', '<=', $start)
                                        ->where('end_date', '>=', $end);
                                });
                        });
                })
                ->orWhere(function ($q) use ($start) {
                    $q->whereNull('end_date')
                        ->where('start_date', '<=', $start);
                });
        });
    }
} 