<?php

namespace App\Notifications;

use App\Models\MaintenanceTask;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaintenanceTaskAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    protected MaintenanceTask $task;

    public function __construct(MaintenanceTask $task)
    {
        $this->task = $task;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Maintenance Task Assigned')
            ->line('You have been assigned a new maintenance task.')
            ->line("Room: {$this->task->room->number}")
            ->line("Category: {$this->task->category->name}")
            ->line("Scheduled Date: {$this->task->scheduled_date->format('Y-m-d H:i')}")
            ->line("Priority Level: {$this->task->category->priority_level}")
            ->action('View Task Details', url("/maintenance/tasks/{$this->task->id}"))
            ->line('Please review the task details and ensure you have all required skills and materials.');
    }

    public function toArray($notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'room_number' => $this->task->room->number,
            'category_name' => $this->task->category->name,
            'scheduled_date' => $this->task->scheduled_date->format('Y-m-d H:i'),
            'priority_level' => $this->task->category->priority_level,
        ];
    }
} 