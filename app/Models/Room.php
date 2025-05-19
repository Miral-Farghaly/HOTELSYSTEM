<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

/**
 * @OA\Schema(
 *     schema="Room",
 *     required={"number", "room_type_id", "floor", "status"},
 *     @OA\Property(property="id", type="integer", format="int64", readOnly=true),
 *     @OA\Property(property="number", type="string", maxLength=10),
 *     @OA\Property(property="room_type_id", type="integer", format="int64"),
 *     @OA\Property(property="floor", type="integer"),
 *     @OA\Property(property="status", type="string", enum={"active", "inactive", "maintenance"}),
 *     @OA\Property(property="description", type="string", nullable=true),
 *     @OA\Property(property="amenities", type="array", @OA\Items(type="string")),
 *     @OA\Property(property="is_maintenance", type="boolean"),
 *     @OA\Property(property="created_at", type="string", format="date-time", readOnly=true),
 *     @OA\Property(property="updated_at", type="string", format="date-time", readOnly=true),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
 * )
 */
class Room extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'number',
        'type',
        'floor',
        'description',
        'price_per_night',
        'base_price',
        'currency',
        'capacity',
        'is_available',
        'needs_maintenance',
        'amenities'
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'needs_maintenance' => 'boolean',
        'price_per_night' => 'decimal:2',
        'base_price' => 'decimal:2',
        'amenities' => 'array'
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(RoomType::class, 'type_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function maintenanceLogs(): HasMany
    {
        return $this->hasMany(MaintenanceLog::class);
    }

    public function priceHistory(): HasMany
    {
        return $this->hasMany(RoomPriceHistory::class);
    }

    public function specialPrices(): HasMany
    {
        return $this->hasMany(RoomSpecialPrice::class);
    }

    public function waitlistEntries(): HasMany
    {
        return $this->hasMany(RoomWaitlist::class);
    }

    public function blockings(): HasMany
    {
        return $this->hasMany(RoomBlock::class);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'active')
            ->where('is_maintenance', false)
            ->where(function ($q) {
                $q->where('is_blocked', false)
                    ->orWhere('block_until', '<', now());
            });
    }

    public function scopeUnderMaintenance($query)
    {
        return $query->where('is_maintenance', true);
    }

    public function isAvailable(): bool
    {
        return $this->status === 'active' && 
               !$this->is_maintenance && 
               !$this->isBlocked();
    }

    public function markForMaintenance(): void
    {
        $this->update([
            'is_maintenance' => true,
            'status' => 'maintenance'
        ]);
    }

    public function markAsAvailable(): void
    {
        $this->update([
            'is_maintenance' => false,
            'status' => 'active'
        ]);
    }

    public function getCurrentPrice(?Carbon $date = null): float
    {
        $date = $date ?? now();
        
        // Check for special event pricing first
        $specialPrice = $this->specialPrices()
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first();
            
        if ($specialPrice) {
            return $specialPrice->price;
        }
        
        return $this->price_per_night;
    }

    public function recordPriceChange(float $oldPrice, float $newPrice, string $reason = null): void
    {
        $this->priceHistory()->create([
            'old_price' => $oldPrice,
            'new_price' => $newPrice,
            'reason' => $reason,
            'changed_by' => auth()->id(),
        ]);
    }

    public function isBlocked(): bool
    {
        if (!$this->is_blocked) {
            return false;
        }

        if ($this->block_until && now()->gt($this->block_until)) {
            $this->unblock();
            return false;
        }

        return true;
    }

    public function block(string $reason, ?Carbon $until = null): void
    {
        $this->update([
            'is_blocked' => true,
            'block_reason' => $reason,
            'block_until' => $until,
        ]);
    }

    public function unblock(): void
    {
        $this->update([
            'is_blocked' => false,
            'block_reason' => null,
            'block_until' => null,
        ]);
    }

    public function addToWaitlist(array $data): RoomWaitlist
    {
        if (!$this->allow_waitlist) {
            throw new \Exception('Waitlist is not enabled for this room');
        }

        return $this->waitlistEntries()->create($data);
    }

    public function canOverbook(): bool
    {
        if ($this->max_overbooking <= 0) {
            return false;
        }

        $currentOverbookings = $this->reservations()
            ->where('is_overbooked', true)
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->count();

        return $currentOverbookings < $this->max_overbooking;
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
} 