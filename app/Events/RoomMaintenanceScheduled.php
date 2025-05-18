<?php

namespace App\Events;

use App\Models\MaintenanceLog;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class RoomMaintenanceScheduled implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public MaintenanceLog $maintenanceLog
    ) {}

    public function broadcastOn(): array
    {
        return ['maintenance'];
    }

    public function broadcastAs(): string
    {
        return 'maintenance.scheduled';
    }

    public function broadcastWith(): array
    {
        return [
            'maintenance_log' => [
                'id' => $this->maintenanceLog->id,
                'room_number' => $this->maintenanceLog->room->number,
                'start_date' => $this->maintenanceLog->start_date->format('Y-m-d H:i:s'),
                'maintenance_type' => $this->maintenanceLog->maintenance_type,
                'description' => $this->maintenanceLog->description,
            ],
        ];
    }
} 