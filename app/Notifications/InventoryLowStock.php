<?php

namespace App\Notifications;

use App\Models\MaintenanceInventory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InventoryLowStock extends Notification implements ShouldQueue
{
    use Queueable;

    protected MaintenanceInventory $item;

    public function __construct(MaintenanceInventory $item)
    {
        $this->item = $item;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Inventory Low Stock Alert')
            ->line("The following inventory item has reached its minimum stock level:")
            ->line("Item: {$this->item->name}")
            ->line("SKU: {$this->item->sku}")
            ->line("Current Quantity: {$this->item->quantity} {$this->item->unit}")
            ->line("Minimum Quantity: {$this->item->minimum_quantity} {$this->item->unit}")
            ->line("Reorder Point: {$this->item->reorder_point} {$this->item->unit}")
            ->action('View Inventory Item', url("/maintenance/inventory/{$this->item->id}"))
            ->line('Please review and reorder if necessary.');
    }

    public function toArray($notifiable): array
    {
        return [
            'item_id' => $this->item->id,
            'name' => $this->item->name,
            'sku' => $this->item->sku,
            'quantity' => $this->item->quantity,
            'minimum_quantity' => $this->item->minimum_quantity,
            'reorder_point' => $this->item->reorder_point,
            'unit' => $this->item->unit,
        ];
    }
} 