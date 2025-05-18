<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaintenanceInventoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'sku' => $this->sku,
            'quantity' => $this->quantity,
            'unit' => $this->unit,
            'minimum_quantity' => $this->minimum_quantity,
            'reorder_point' => $this->reorder_point,
            'category' => $this->category,
            'location' => $this->location,
            'supplier_info' => $this->supplier_info,
            'needs_reorder' => $this->needsReorder(),
            'is_low_stock' => $this->isLowStock(),
            'value' => $this->value,
            'inventory_logs' => $this->whenLoaded('inventoryLogs', fn() => MaintenanceInventoryLogResource::collection($this->inventoryLogs)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 