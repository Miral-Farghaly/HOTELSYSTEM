<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\MaintenanceTask;

class MaintenanceTaskCompleted extends Notification
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
            ->subject('Maintenance Task Completed')
            ->line('A maintenance task has been completed.')
            ->line('Task: ' . $this->task->description)
            ->line('Room: ' . $this->task->room->number)
            ->line('Completion Date: ' . now())
            ->action('View Details', url('/maintenance/tasks/' . $this->task->id));
    }

    public function toArray($notifiable)
    {
        return [
            'task_id' => $this->task->id,
            'description' => $this->task->description,
            'room_number' => $this->task->room->number,
            'completion_date' => now(),
        ];
    }
} 