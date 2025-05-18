<?php

namespace Tests\Feature;

use App\Models\MaintenanceCategory;
use App\Models\MaintenanceTask;
use App\Models\MaintenanceInventory;
use App\Models\Room;
use App\Models\User;
use App\Models\StaffSkill;
use App\Notifications\MaintenanceTaskAssigned;
use App\Notifications\MaintenanceTaskCompleted;
use App\Notifications\InventoryLowStock;
use App\Notifications\SkillVerificationNeeded;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MaintenanceNotificationsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Room $room;
    protected MaintenanceCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->room = Room::factory()->create();
        $this->category = MaintenanceCategory::create([
            'name' => 'Test Category',
            'description' => 'Test Description',
            'estimated_duration' => 60,
            'required_skills' => ['plumbing'],
            'priority_level' => 1,
        ]);
    }

    public function test_sends_task_assigned_notification()
    {
        Notification::fake();

        $task = MaintenanceTask::create([
            'room_id' => $this->room->id,
            'category_id' => $this->category->id,
            'assigned_to' => $this->user->id,
            'scheduled_date' => now()->addDay(),
            'status' => 'pending',
        ]);

        $this->user->notify(new MaintenanceTaskAssigned($task));

        Notification::assertSentTo(
            $this->user,
            MaintenanceTaskAssigned::class,
            function ($notification) use ($task) {
                return $notification->task->id === $task->id;
            }
        );
    }

    public function test_sends_task_completed_notification()
    {
        Notification::fake();

        $task = MaintenanceTask::create([
            'room_id' => $this->room->id,
            'category_id' => $this->category->id,
            'assigned_to' => $this->user->id,
            'scheduled_date' => now(),
            'status' => 'completed',
            'completed_at' => now(),
            'actual_duration' => 45,
            'notes' => 'Task completed successfully',
        ]);

        $this->user->notify(new MaintenanceTaskCompleted($task));

        Notification::assertSentTo(
            $this->user,
            MaintenanceTaskCompleted::class,
            function ($notification) use ($task) {
                return $notification->task->id === $task->id;
            }
        );
    }

    public function test_sends_inventory_low_stock_notification()
    {
        Notification::fake();

        $item = MaintenanceInventory::create([
            'name' => 'Test Item',
            'sku' => 'TEST001',
            'quantity' => 3,
            'unit' => 'piece',
            'minimum_quantity' => 5,
            'reorder_point' => 7,
        ]);

        $this->user->notify(new InventoryLowStock($item));

        Notification::assertSentTo(
            $this->user,
            InventoryLowStock::class,
            function ($notification) use ($item) {
                return $notification->item->id === $item->id;
            }
        );
    }

    public function test_sends_skill_verification_needed_notification()
    {
        Notification::fake();

        $skill = StaffSkill::create([
            'user_id' => $this->user->id,
            'skill_name' => 'plumbing',
            'level' => 'intermediate',
            'description' => 'Basic plumbing skills',
        ]);

        $skill->verifications()->create([
            'verified_by' => $this->user->id,
            'verification_date' => now()->subMonths(6),
            'next_verification_date' => now()->subDay(),
        ]);

        $this->user->notify(new SkillVerificationNeeded($skill));

        Notification::assertSentTo(
            $this->user,
            SkillVerificationNeeded::class,
            function ($notification) use ($skill) {
                return $notification->skill->id === $skill->id;
            }
        );
    }
} 