<?php

namespace App\Console\Commands;

use App\Models\Room;
use App\Models\MaintenanceLog;
use App\Events\RoomMaintenanceScheduled;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckRoomMaintenance extends Command
{
    protected $signature = 'rooms:check-maintenance {--days=90 : Days since last maintenance}';
    protected $description = 'Check and notify about rooms due for maintenance';

    public function handle(): void
    {
        $daysThreshold = $this->option('days');
        
        $rooms = Room::where(function ($query) use ($daysThreshold) {
                $query->whereDoesntHave('maintenanceLogs')
                    ->orWhereHas('maintenanceLogs', function ($q) use ($daysThreshold) {
                        $q->where('status', 'completed')
                            ->where('end_date', '<=', now()->subDays($daysThreshold));
                    });
            })
            ->where('status', 'active')
            ->where('is_maintenance', false)
            ->get();

        $this->info("Found {$rooms->count()} rooms due for maintenance");

        foreach ($rooms as $room) {
            try {
                $maintenanceLog = MaintenanceLog::create([
                    'room_id' => $room->id,
                    'user_id' => 1, // System user
                    'description' => 'Scheduled routine maintenance',
                    'maintenance_type' => 'routine',
                    'start_date' => now()->addDays(1)->startOfDay(),
                    'status' => 'scheduled',
                ]);

                event(new RoomMaintenanceScheduled($maintenanceLog));

                $this->info("Scheduled maintenance for Room {$room->number}");
                
                Log::info('Maintenance scheduled', [
                    'room_id' => $room->id,
                    'room_number' => $room->number,
                    'maintenance_log_id' => $maintenanceLog->id,
                ]);
            } catch (\Exception $e) {
                $this->error("Failed to schedule maintenance for Room {$room->number}");
                Log::error('Failed to schedule maintenance', [
                    'room_id' => $room->id,
                    'room_number' => $room->number,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
} 