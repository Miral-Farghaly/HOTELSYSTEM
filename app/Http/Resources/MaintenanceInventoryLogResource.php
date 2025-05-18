<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaintenanceInventoryLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'inventory_item' => $this->whenLoaded('inventory', fn() => [
                'id' => $this->inventory->id,
                'name' => $this->inventory->name,
                'sku' => $this->inventory->sku,
            ]),
            'user' => $this->whenLoaded('user', fn() => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ]),
            'type' => $this->type,
            'quantity' => $this->quantity,
            'reason' => $this->reason,
            'maintenance_task' => $this->whenLoaded('maintenanceTask', fn() => [
                'id' => $this->maintenanceTask->id,
                'room_number' => $this->maintenanceTask->room->number,
            ]),
            'unit_cost' => $this->unit_cost,
            'total_cost' => $this->total_cost,
            'created_at' => $this->created_at,
        ];
    }
} 