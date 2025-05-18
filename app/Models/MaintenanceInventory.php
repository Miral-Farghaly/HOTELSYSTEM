<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *     schema="MaintenanceInventory",
 *     required={"name", "sku", "quantity", "unit"},
 *     @OA\Property(property="id", type="integer", format="int64", readOnly=true),
 *     @OA\Property(property="name", type="string", maxLength=255),
 *     @OA\Property(property="sku", type="string", maxLength=50),
 *     @OA\Property(property="description", type="string", nullable=true),
 *     @OA\Property(property="quantity", type="number", format="float"),
 *     @OA\Property(property="unit", type="string", maxLength=20),
 *     @OA\Property(property="min_quantity", type="number", format="float"),
 *     @OA\Property(property="reorder_point", type="number", format="float"),
 *     @OA\Property(property="category", type="string", nullable=true),
 *     @OA\Property(property="location", type="string", nullable=true),
 *     @OA\Property(property="supplier_info", type="object", nullable=true),
 *     @OA\Property(property="last_ordered_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", readOnly=true),
 *     @OA\Property(property="updated_at", type="string", format="date-time", readOnly=true),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
 * )
 */
class MaintenanceInventory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'maintenance_inventory';

    protected $fillable = [
        'name',
        'description',
        'sku',
        'quantity',
        'minimum_quantity',
        'reorder_point',
        'unit',
        'unit_cost',
        'location',
        'suppliers',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'minimum_quantity' => 'integer',
        'reorder_point' => 'integer',
        'unit_cost' => 'decimal:2',
        'suppliers' => 'array',
    ];

    public function inventoryLogs(): HasMany
    {
        return $this->hasMany(MaintenanceInventoryLog::class, 'inventory_id');
    }

    public function needsReorder(): bool
    {
        return $this->quantity <= $this->reorder_point;
    }

    public function isLowStock(): bool
    {
        return $this->quantity <= $this->minimum_quantity;
    }

    public function adjustStock(int $quantity, string $type, string $reason = null, ?int $taskId = null): void
    {
        $this->inventoryLogs()->create([
            'type' => $type,
            'quantity' => $quantity,
            'reason' => $reason,
            'maintenance_task_id' => $taskId,
            'user_id' => auth()->id(),
            'unit_cost' => $this->unit_cost,
        ]);

        if ($type === 'in') {
            $this->increment('quantity', $quantity);
        } elseif ($type === 'out') {
            $this->decrement('quantity', $quantity);
        }
    }

    public function scopeLowStock($query)
    {
        return $query->whereRaw('quantity <= minimum_quantity');
    }

    public function scopeNeedsReorder($query)
    {
        return $query->whereRaw('quantity <= reorder_point');
    }

    public function getValueAttribute(): float
    {
        return $this->quantity * $this->unit_cost;
    }

    public function usageLogs(): HasMany
    {
        return $this->hasMany(MaintenanceInventoryLog::class, 'inventory_id');
    }

    public function adjustQuantity(float $amount, string $reason, ?MaintenanceTask $task = null): void
    {
        $this->usageLogs()->create([
            'quantity_change' => $amount,
            'reason' => $reason,
            'task_id' => $task?->id,
            'previous_quantity' => $this->quantity,
            'new_quantity' => $this->quantity + $amount,
        ]);

        $this->increment('quantity', $amount);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByLocation($query, string $location)
    {
        return $query->where('location', $location);
    }
} 