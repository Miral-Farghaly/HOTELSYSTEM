<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained('maintenance_categories');
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->foreignId('parent_task_id')->nullable()->constrained('maintenance_tasks');
            $table->timestamp('scheduled_date');
            $table->timestamp('completed_at')->nullable();
            $table->string('status');
            $table->text('notes')->nullable();
            $table->integer('actual_duration')->nullable()->comment('Duration in minutes');
            $table->json('items_used')->nullable();
            $table->string('recurrence_rule')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['room_id', 'status']);
            $table->index(['category_id', 'status']);
            $table->index(['assigned_to', 'status']);
            $table->index('scheduled_date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_tasks');
    }
}; 