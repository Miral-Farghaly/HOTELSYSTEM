<?php

namespace App\Listeners;

use App\Events\RoomMaintenanceScheduled;
use App\Models\User;
use App\Notifications\MaintenanceScheduled;
use Illuminate\Support\Facades\Notification;

class NotifyMaintenanceTeam
{
    public function handle(RoomMaintenanceScheduled $event): void
    {
        $maintenanceTeam = User::role('maintenance')->get();
        
        Notification::send($maintenanceTeam, new MaintenanceScheduled($event->maintenanceLog));

        // Log the notification
        \Log::info('Maintenance team notified', [
            'maintenance_log_id' => $event->maintenanceLog->id,
            'room_number' => $event->maintenanceLog->room->number,
            'team_members' => $maintenanceTeam->pluck('id')->toArray(),
        ]);
    }

    public function shouldQueue(): bool
    {
        return true;
    }

    public function viaQueue(): string
    {
        return 'notifications';
    }
} 