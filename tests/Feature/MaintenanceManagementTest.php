<?php

namespace Tests\Feature;

use App\Models\MaintenanceCategory;
use App\Models\MaintenanceTask;
use App\Models\MaintenanceInventory;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceManagementTest extends TestCase
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

    public function test_can_create_maintenance_task()
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/maintenance/tasks', [
            'room_id' => $this->room->id,
            'category_id' => $this->category->id,
            'scheduled_date' => now()->addDay(),
            'status' => 'pending',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'room',
                    'category',
                    'scheduled_date',
                    'status',
                ],
            ]);

        $this->assertDatabaseHas('maintenance_tasks', [
            'room_id' => $this->room->id,
            'category_id' => $this->category->id,
            'status' => 'pending',
        ]);
    }

    public function test_can_complete_maintenance_task()
    {
        $task = MaintenanceTask::create([
            'room_id' => $this->room->id,
            'category_id' => $this->category->id,
            'scheduled_date' => now()->addDay(),
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($this->user)->postJson("/api/v1/maintenance/tasks/{$task->id}/complete", [
            'actual_duration' => 45,
            'notes' => 'Task completed successfully',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'status',
                    'completed_at',
                    'actual_duration',
                    'notes',
                ],
            ]);

        $this->assertDatabaseHas('maintenance_tasks', [
            'id' => $task->id,
            'status' => 'completed',
            'actual_duration' => 45,
            'notes' => 'Task completed successfully',
        ]);
    }

    public function test_can_manage_inventory()
    {
        $item = MaintenanceInventory::create([
            'name' => 'Test Item',
            'sku' => 'TEST001',
            'quantity' => 10,
            'unit' => 'piece',
            'minimum_quantity' => 5,
            'reorder_point' => 7,
        ]);

        $response = $this->actingAs($this->user)->postJson("/api/v1/maintenance/inventory/{$item->id}/adjust", [
            'quantity' => 3,
            'type' => 'out',
            'reason' => 'Used in maintenance',
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('maintenance_inventory', [
            'id' => $item->id,
            'quantity' => 7,
        ]);

        $this->assertDatabaseHas('maintenance_inventory_logs', [
            'inventory_id' => $item->id,
            'quantity' => 3,
            'type' => 'out',
            'reason' => 'Used in maintenance',
        ]);
    }

    public function test_can_manage_staff_skills()
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/staff/skills', [
            'user_id' => $this->user->id,
            'skill_name' => 'plumbing',
            'level' => 'intermediate',
            'description' => 'Basic plumbing skills',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user',
                    'skill_name',
                    'level',
                    'description',
                ],
            ]);

        $this->assertDatabaseHas('staff_skills', [
            'user_id' => $this->user->id,
            'skill_name' => 'plumbing',
            'level' => 'intermediate',
        ]);
    }

    public function test_can_verify_staff_skill()
    {
        $skill = $this->user->staffSkills()->create([
            'skill_name' => 'plumbing',
            'level' => 'intermediate',
            'description' => 'Basic plumbing skills',
        ]);

        $response = $this->actingAs($this->user)->postJson("/api/v1/staff/skills/{$skill->id}/verify", [
            'notes' => 'Skill verified through practical demonstration',
            'next_verification_date' => now()->addMonths(6)->format('Y-m-d'),
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'verifications' => [
                        '*' => [
                            'id',
                            'verified_by',
                            'notes',
                            'verification_date',
                            'next_verification_date',
                        ],
                    ],
                ],
            ]);

        $this->assertDatabaseHas('skill_verifications', [
            'staff_skill_id' => $skill->id,
            'verified_by' => $this->user->id,
            'notes' => 'Skill verified through practical demonstration',
        ]);
    }
} 