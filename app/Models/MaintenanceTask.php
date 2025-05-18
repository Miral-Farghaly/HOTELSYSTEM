<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

/**
 * @OA\Schema(
 *     schema="MaintenanceTask",
 *     required={"room_id", "category_id", "scheduled_date", "status"},
 *     @OA\Property(property="id", type="integer", format="int64", readOnly=true),
 *     @OA\Property(property="room_id", type="integer", format="int64"),
 *     @OA\Property(property="category_id", type="integer", format="int64"),
 *     @OA\Property(property="assigned_to", type="integer", format="int64", nullable=true),
 *     @OA\Property(property="scheduled_date", type="string", format="date-time"),
 *     @OA\Property(property="completed_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="status", type="string", enum={"pending", "in_progress", "completed", "cancelled"}),
 *     @OA\Property(property="notes", type="string", nullable=true),
 *     @OA\Property(property="actual_duration", type="integer", nullable=true),
 *     @OA\Property(property="items_used", type="array", @OA\Items(type="object")),
 *     @OA\Property(property="created_at", type="string", format="date-time", readOnly=true),
 *     @OA\Property(property="updated_at", type="string", format="date-time", readOnly=true),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
 * )
 */
class MaintenanceTask extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'room_id',
        'category_id',
        'assigned_to',
        'scheduled_date',
        'completed_at',
        'status',
        'notes',
        'actual_duration',
        'items_used',
        'parent_task_id',
        'recurrence_rule',
    ];

    protected $casts = [
        'scheduled_date' => 'datetime',
        'completed_at' => 'datetime',
        'actual_duration' => 'integer',
        'items_used' => 'array',
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MaintenanceCategory::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function parentTask(): BelongsTo
    {
        return $this->belongsTo(MaintenanceTask::class, 'parent_task_id');
    }

    public function childTasks(): HasMany
    {
        return $this->hasMany(MaintenanceTask::class, 'parent_task_id');
    }

    public function inventoryLogs(): HasMany
    {
        return $this->hasMany(MaintenanceInventoryLog::class);
    }

    public function start(): void
    {
        if ($this->status !== 'pending') {
            throw new \Exception('Task can only be started from pending status');
        }

        $this->update(['status' => 'in_progress']);
    }

    public function complete(array $data): void
    {
        if ($this->status !== 'in_progress') {
            throw new \Exception('Task can only be completed from in_progress status');
        }

        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'actual_duration' => $data['actual_duration'] ?? null,
            'items_used' => $data['items_used'] ?? [],
            'notes' => $data['notes'] ?? $this->notes,
        ]);

        // Create next recurring task if applicable
        if ($this->recurrence_rule) {
            $this->createNextRecurringTask();
        }
    }

    public function cancel(string $reason): void
    {
        if ($this->status === 'completed') {
            throw new \Exception('Completed tasks cannot be cancelled');
        }

        $this->update([
            'status' => 'cancelled',
            'notes' => $reason,
        ]);
    }

    public function createNextRecurringTask(): void
    {
        if (!$this->recurrence_rule) {
            return;
        }

        $nextDate = $this->calculateNextRecurrence();
        if (!$nextDate) {
            return;
        }

        $this->childTasks()->create([
            'room_id' => $this->room_id,
            'category_id' => $this->category_id,
            'scheduled_date' => $nextDate,
            'recurrence_rule' => $this->recurrence_rule,
        ]);
    }

    protected function calculateNextRecurrence(): ?Carbon
    {
        // Parse recurrence rule and calculate next date
        // Example rule: "FREQ=DAILY;INTERVAL=1" or "FREQ=WEEKLY;INTERVAL=2;BYDAY=MO,WE,FR"
        // This is a simplified version, you might want to use a proper recurrence rule parser
        $rules = collect(explode(';', $this->recurrence_rule))
            ->mapWithKeys(function ($rule) {
                $parts = explode('=', $rule);
                return [$parts[0] => $parts[1]];
            });

        $date = $this->scheduled_date->copy();
        
        switch ($rules->get('FREQ')) {
            case 'DAILY':
                return $date->addDays($rules->get('INTERVAL', 1));
            case 'WEEKLY':
                return $date->addWeeks($rules->get('INTERVAL', 1));
            case 'MONTHLY':
                return $date->addMonths($rules->get('INTERVAL', 1));
            default:
                return null;
        }
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'pending')
            ->where('scheduled_date', '<', now());
    }

    public function scopeForDateRange($query, Carbon $start, Carbon $end)
    {
        return $query->whereBetween('scheduled_date', [$start, $end]);
    }

    public function scopeRecurring($query)
    {
        return $query->whereNotNull('recurrence_rule');
    }
} 