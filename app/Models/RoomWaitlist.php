<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

/**
 * @OA\Schema(
 *     schema="RoomWaitlist",
 *     required={"room_id", "user_id", "check_in", "check_out"},
 *     @OA\Property(property="id", type="integer", format="int64", readOnly=true),
 *     @OA\Property(property="room_id", type="integer", format="int64"),
 *     @OA\Property(property="user_id", type="integer", format="int64"),
 *     @OA\Property(property="check_in", type="string", format="date"),
 *     @OA\Property(property="check_out", type="string", format="date"),
 *     @OA\Property(property="notes", type="string", nullable=true),
 *     @OA\Property(property="status", type="string", enum={"waiting", "notified", "expired", "converted"}),
 *     @OA\Property(property="last_notified_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", readOnly=true),
 *     @OA\Property(property="updated_at", type="string", format="date-time", readOnly=true)
 * )
 */
class RoomWaitlist extends Model
{
    protected $fillable = [
        'room_id',
        'user_id',
        'check_in',
        'check_out',
        'notes',
        'status',
        'last_notified_at',
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'last_notified_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'waiting',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->check_in->isPast() || $this->status === 'expired';
    }

    public function markAsNotified(): void
    {
        $this->update([
            'status' => 'notified',
            'last_notified_at' => now(),
        ]);
    }

    public function markAsConverted(): void
    {
        $this->update(['status' => 'converted']);
    }

    public function markAsExpired(): void
    {
        $this->update(['status' => 'expired']);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'waiting')
            ->where('check_in', '>=', now());
    }

    public function scopeForDateRange($query, Carbon $start, Carbon $end)
    {
        return $query->where(function ($q) use ($start, $end) {
            $q->whereBetween('check_in', [$start, $end])
                ->orWhereBetween('check_out', [$start, $end])
                ->orWhere(function ($q) use ($start, $end) {
                    $q->where('check_in', '<=', $start)
                        ->where('check_out', '>=', $end);
                });
        });
    }
} 