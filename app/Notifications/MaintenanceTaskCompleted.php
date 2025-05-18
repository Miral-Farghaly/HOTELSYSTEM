<?php

namespace App\Notifications;

use App\Models\MaintenanceTask;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaintenanceTaskCompleted extends Notification implements ShouldQueue
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
            ->subject('Maintenance Task Completed')
            ->line('A maintenance task has been completed.')
            ->line("Room: {$this->task->room->number}")
            ->line("Category: {$this->task->category->name}")
            ->line("Completed At: {$this->task->completed_at->format('Y-m-d H:i')}")
            ->line("Duration: {$this->task->actual_duration} minutes")
            ->line("Notes: {$this->task->notes}")
            ->action('View Task Details', url("/maintenance/tasks/{$this->task->id}"))
            ->line('Please review the completion details and verify if any follow-up is needed.');
    }

    public function toArray($notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'room_number' => $this->task->room->number,
            'category_name' => $this->task->category->name,
            'completed_at' => $this->task->completed_at->format('Y-m-d H:i'),
            'actual_duration' => $this->task->actual_duration,
            'notes' => $this->task->notes,
        ];
    }
} 