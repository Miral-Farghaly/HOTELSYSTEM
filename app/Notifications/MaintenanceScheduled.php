<?php

namespace App\Notifications;

use App\Models\MaintenanceLog;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class MaintenanceScheduled extends Notification
{
    use Queueable;

    public function __construct(
        protected MaintenanceLog $maintenanceLog
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Maintenance Task Scheduled')
            ->greeting('Hello ' . $notifiable->name)
            ->line('A new maintenance task has been scheduled.')
            ->line('Room: ' . $this->maintenanceLog->room->number)
            ->line('Type: ' . $this->maintenanceLog->maintenance_type)
            ->line('Description: ' . $this->maintenanceLog->description)
            ->line('Start Date: ' . $this->maintenanceLog->start_date->format('Y-m-d H:i:s'))
            ->action('View Details', route('maintenance.show', $this->maintenanceLog->id))
            ->line('Please ensure this task is completed on time.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'maintenance_log_id' => $this->maintenanceLog->id,
            'room_number' => $this->maintenanceLog->room->number,
            'maintenance_type' => $this->maintenanceLog->maintenance_type,
            'start_date' => $this->maintenanceLog->start_date->format('Y-m-d H:i:s'),
            'description' => $this->maintenanceLog->description,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'maintenance_log_id' => $this->maintenanceLog->id,
            'room_number' => $this->maintenanceLog->room->number,
            'maintenance_type' => $this->maintenanceLog->maintenance_type,
            'start_date' => $this->maintenanceLog->start_date->format('Y-m-d H:i:s'),
            'description' => $this->maintenanceLog->description,
        ]);
    }
} 