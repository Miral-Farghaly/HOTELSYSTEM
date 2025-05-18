<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'type' => new RoomTypeResource($this->whenLoaded('type')),
            'floor' => $this->floor,
            'status' => $this->status,
            'is_maintenance' => $this->is_maintenance,
            'description' => $this->description,
            'price_per_night' => $this->price_per_night,
            'capacity' => $this->capacity,
            'amenities' => $this->amenities,
            'current_maintenance' => $this->whenLoaded('maintenanceLogs', function () {
                return new MaintenanceLogResource(
                    $this->maintenanceLogs
                        ->where('status', '!=', 'completed')
                        ->first()
                );
            }),
            'availability' => [
                'is_available' => $this->isAvailable(),
                'next_available_date' => $this->whenLoaded('reservations', function () {
                    $nextReservation = $this->reservations()
                        ->where('check_out', '>', now())
                        ->orderBy('check_out', 'desc')
                        ->first();
                    
                    return $nextReservation ? $nextReservation->check_out : null;
                }),
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'links' => [
                'self' => route('api.rooms.show', $this->id),
                'reservations' => route('api.rooms.reservations', $this->id),
                'maintenance_history' => route('api.rooms.maintenance-logs', $this->id),
            ],
            'meta' => [
                'is_available' => $this->isAvailable(),
                'last_maintenance' => $this->maintenanceLogs()->latest()->first()?->created_at,
                'total_reservations' => $this->reservations()->count(),
            ],
        ];
    }

    public function with(Request $request): array
    {
        return [
            'status' => 'success',
            'version' => '1.0',
        ];
    }
} 