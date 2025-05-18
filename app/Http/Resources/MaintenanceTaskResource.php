<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaintenanceTaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'room' => $this->whenLoaded('room', fn() => [
                'id' => $this->room->id,
                'number' => $this->room->number,
                'type' => $this->room->type,
            ]),
            'category' => $this->whenLoaded('category', fn() => new MaintenanceCategoryResource($this->category)),
            'assigned_to' => $this->whenLoaded('assignedTo', fn() => [
                'id' => $this->assignedTo->id,
                'name' => $this->assignedTo->name,
            ]),
            'scheduled_date' => $this->scheduled_date,
            'completed_at' => $this->completed_at,
            'status' => $this->status,
            'notes' => $this->notes,
            'actual_duration' => $this->actual_duration,
            'items_used' => $this->items_used,
            'parent_task_id' => $this->parent_task_id,
            'recurrence_rule' => $this->recurrence_rule,
            'child_tasks' => $this->whenLoaded('childTasks', fn() => MaintenanceTaskResource::collection($this->childTasks)),
            'inventory_logs' => $this->whenLoaded('inventoryLogs', fn() => MaintenanceInventoryLogResource::collection($this->inventoryLogs)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 