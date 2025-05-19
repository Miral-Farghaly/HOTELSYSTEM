<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\MaintenanceTask;

class MaintenanceTaskAssigned extends Notification
{
    use Queueable;

    public $task;

    public function __construct(MaintenanceTask $task)
    {
        $this->task = $task;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Maintenance Task Assigned')
            ->line('You have been assigned a new maintenance task.')
            ->line('Task: ' . $this->task->description)
            ->line('Room: ' . $this->task->room->number)
            ->line('Due Date: ' . $this->task->scheduled_date)
            ->action('View Task', url('/maintenance/tasks/' . $this->task->id));
    }

    public function toArray($notifiable)
    {
        return [
            'task_id' => $this->task->id,
            'description' => $this->task->description,
            'room_number' => $this->task->room->number,
            'scheduled_date' => $this->task->scheduled_date,
        ];
    }
} 