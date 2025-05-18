<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *     schema="MaintenanceInventoryLog",
 *     required={"inventory_id", "quantity_change", "previous_quantity", "new_quantity"},
 *     @OA\Property(property="id", type="integer", format="int64", readOnly=true),
 *     @OA\Property(property="inventory_id", type="integer", format="int64"),
 *     @OA\Property(property="task_id", type="integer", format="int64", nullable=true),
 *     @OA\Property(property="quantity_change", type="number", format="float"),
 *     @OA\Property(property="previous_quantity", type="number", format="float"),
 *     @OA\Property(property="new_quantity", type="number", format="float"),
 *     @OA\Property(property="reason", type="string"),
 *     @OA\Property(property="performed_by", type="integer", format="int64"),
 *     @OA\Property(property="created_at", type="string", format="date-time", readOnly=true),
 *     @OA\Property(property="updated_at", type="string", format="date-time", readOnly=true)
 * )
 */
class MaintenanceInventoryLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_id',
        'user_id',
        'type',
        'quantity',
        'reason',
        'maintenance_task_id',
        'unit_cost',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_cost' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($log) {
            $log->performed_by = $log->performed_by ?? auth()->id();
        });
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(MaintenanceInventory::class, 'inventory_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function maintenanceTask(): BelongsTo
    {
        return $this->belongsTo(MaintenanceLog::class, 'maintenance_task_id');
    }

    public function getTotalCostAttribute(): float
    {
        return $this->quantity * $this->unit_cost;
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByDateRange($query, string $start, string $end)
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }

    public function scopeForDateRange($query, $start, $end)
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }

    public function scopeByTask($query, MaintenanceTask $task)
    {
        return $query->where('task_id', $task->id);
    }

    public function scopeByInventory($query, MaintenanceInventory $inventory)
    {
        return $query->where('inventory_id', $inventory->id);
    }

    public function scopeDeductions($query)
    {
        return $query->where('quantity_change', '<', 0);
    }

    public function scopeAdditions($query)
    {
        return $query->where('quantity_change', '>', 0);
    }
} 