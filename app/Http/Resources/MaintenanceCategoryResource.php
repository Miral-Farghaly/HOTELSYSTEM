<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaintenanceCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'estimated_duration' => $this->estimated_duration,
            'required_skills' => $this->required_skills,
            'required_items' => $this->required_items,
            'priority_level' => $this->priority_level,
            'is_recurring' => $this->is_recurring,
            'recurrence_pattern' => $this->recurrence_pattern,
            'tasks_count' => $this->whenLoaded('maintenanceTasks', fn() => $this->maintenanceTasks->count()),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 