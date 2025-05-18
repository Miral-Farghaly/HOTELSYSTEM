<?php

namespace App\Services;

use App\Models\MaintenanceLog;
use App\Models\MaintenanceCategory;
use App\Models\MaintenanceInventory;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class MaintenanceService
{
    public function createMaintenanceTask(array $data): MaintenanceLog
    {
        return DB::transaction(function () use ($data) {
            // Validate staff skills if category requires specific skills
            if (isset($data['category_id'])) {
                $category = MaintenanceCategory::findOrFail($data['category_id']);
                $this->validateStaffSkills($data['assigned_staff'] ?? [], $category->required_skills);
            }

            // Create the maintenance task
            $task = MaintenanceLog::create($data);

            // If it's recurring, schedule future occurrences
            if ($task->is_recurring && $task->recurrence_pattern) {
                $this->scheduleRecurringTasks($task);
            }

            // Reserve required items
            if (!empty($data['required_items'])) {
                $this->reserveInventoryItems($task, $data['required_items']);
            }

            // Update room status
            if ($task->start_date->isToday()) {
                $task->room->markForMaintenance();
            }

            return $task;
        });
    }

    public function updateMaintenanceTask(MaintenanceLog $task, array $data): MaintenanceLog
    {
        return DB::transaction(function () use ($task, $data) {
            // Update inventory reservations if required items changed
            if (isset($data['required_items']) && $data['required_items'] !== $task->required_items) {
                $this->updateInventoryReservations($task, $data['required_items']);
            }

            // Update task details
            $task->update($data);

            // Update room status if dates changed
            if (isset($data['start_date']) || isset($data['end_date'])) {
                $this->updateRoomStatus($task);
            }

            return $task;
        });
    }

    public function completeMaintenanceTask(MaintenanceLog $task, array $data): MaintenanceLog
    {
        return DB::transaction(function () use ($task, $data) {
            // Record used items
            if (!empty($data['used_items'])) {
                $this->recordUsedItems($task, $data['used_items']);
            }

            // Update task completion details
            $task->update([
                'status' => 'completed',
                'completed_at' => now(),
                'completion_notes' => $data['completion_notes'] ?? null,
                'duration_minutes' => $data['duration_minutes'] ?? null,
                'labor_cost' => $data['labor_cost'] ?? null,
                'material_cost' => $data['material_cost'] ?? null,
                'used_items' => $data['used_items'] ?? null,
                'checklist' => $data['checklist'] ?? null,
            ]);

            // Update room status
            $task->room->markAsAvailable();

            return $task;
        });
    }

    public function getUpcomingMaintenanceTasks(int $days = 7): Collection
    {
        return MaintenanceLog::where('status', 'scheduled')
            ->where('start_date', '>=', now())
            ->where('start_date', '<=', now()->addDays($days))
            ->with(['room', 'category'])
            ->orderBy('start_date')
            ->get();
    }

    public function getOverdueTasks(): Collection
    {
        return MaintenanceLog::where('status', 'scheduled')
            ->where('start_date', '<', now())
            ->with(['room', 'category'])
            ->orderBy('start_date')
            ->get();
    }

    public function generateMaintenanceReport(string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $tasks = MaintenanceLog::whereBetween('start_date', [$start, $end])
            ->with(['room', 'category'])
            ->get();

        return [
            'total_tasks' => $tasks->count(),
            'completed_tasks' => $tasks->where('status', 'completed')->count(),
            'total_duration' => $tasks->sum('duration_minutes'),
            'total_labor_cost' => $tasks->sum('labor_cost'),
            'total_material_cost' => $tasks->sum('material_cost'),
            'by_category' => $this->getTasksByCategory($tasks),
            'by_room' => $this->getTasksByRoom($tasks),
            'inventory_usage' => $this->getInventoryUsage($start, $end),
        ];
    }

    protected function validateStaffSkills(array $staffIds, array $requiredSkills): void
    {
        // Implementation depends on how staff skills are stored
        // This is a placeholder for the validation logic
    }

    protected function scheduleRecurringTasks(MaintenanceLog $task): void
    {
        // Parse recurrence pattern and create future tasks
        // This is a placeholder for the recurrence logic
    }

    protected function reserveInventoryItems(MaintenanceLog $task, array $items): void
    {
        foreach ($items as $item) {
            $inventory = MaintenanceInventory::findOrFail($item['id']);
            
            if ($inventory->quantity < $item['quantity']) {
                throw new \Exception("Insufficient quantity for item: {$inventory->name}");
            }

            $inventory->adjustStock(
                $item['quantity'],
                'out',
                "Reserved for maintenance task #{$task->id}",
                $task->id
            );
        }
    }

    protected function updateInventoryReservations(MaintenanceLog $task, array $newItems): void
    {
        // Return previously reserved items
        if ($task->required_items) {
            foreach ($task->required_items as $item) {
                $inventory = MaintenanceInventory::findOrFail($item['id']);
                $inventory->adjustStock(
                    $item['quantity'],
                    'in',
                    "Returned from maintenance task #{$task->id}",
                    $task->id
                );
            }
        }

        // Reserve new items
        $this->reserveInventoryItems($task, $newItems);
    }

    protected function recordUsedItems(MaintenanceLog $task, array $items): void
    {
        foreach ($items as $item) {
            $inventory = MaintenanceInventory::findOrFail($item['id']);
            $inventory->adjustStock(
                $item['quantity'],
                'out',
                "Used in maintenance task #{$task->id}",
                $task->id
            );
        }
    }

    protected function updateRoomStatus(MaintenanceLog $task): void
    {
        if ($task->start_date->isToday() && !$task->room->is_maintenance) {
            $task->room->markForMaintenance();
        } elseif ($task->end_date->isPast() && $task->room->is_maintenance) {
            $task->room->markAsAvailable();
        }
    }

    protected function getTasksByCategory(Collection $tasks): array
    {
        return $tasks->groupBy('category_id')
            ->map(function ($categoryTasks) {
                return [
                    'total' => $categoryTasks->count(),
                    'completed' => $categoryTasks->where('status', 'completed')->count(),
                    'duration' => $categoryTasks->sum('duration_minutes'),
                    'labor_cost' => $categoryTasks->sum('labor_cost'),
                    'material_cost' => $categoryTasks->sum('material_cost'),
                ];
            })
            ->toArray();
    }

    protected function getTasksByRoom(Collection $tasks): array
    {
        return $tasks->groupBy('room_id')
            ->map(function ($roomTasks) {
                return [
                    'total' => $roomTasks->count(),
                    'completed' => $roomTasks->where('status', 'completed')->count(),
                    'duration' => $roomTasks->sum('duration_minutes'),
                    'labor_cost' => $roomTasks->sum('labor_cost'),
                    'material_cost' => $roomTasks->sum('material_cost'),
                ];
            })
            ->toArray();
    }

    protected function getInventoryUsage(Carbon $start, Carbon $end): array
    {
        return MaintenanceInventory::with(['inventoryLogs' => function ($query) use ($start, $end) {
            $query->whereBetween('created_at', [$start, $end]);
        }])
        ->get()
        ->map(function ($inventory) {
            $usage = $inventory->inventoryLogs->where('type', 'out');
            return [
                'item' => $inventory->name,
                'quantity_used' => $usage->sum('quantity'),
                'total_cost' => $usage->sum('total_cost'),
            ];
        })
        ->toArray();
    }
} 