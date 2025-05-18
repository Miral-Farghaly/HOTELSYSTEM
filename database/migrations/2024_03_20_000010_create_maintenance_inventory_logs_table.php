<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_inventory_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained('maintenance_inventories')->onDelete('cascade');
            $table->foreignId('task_id')->nullable()->constrained('maintenance_tasks')->onDelete('set null');
            $table->decimal('quantity_change', 10, 2);
            $table->decimal('previous_quantity', 10, 2);
            $table->decimal('new_quantity', 10, 2);
            $table->string('reason');
            $table->foreignId('performed_by')->constrained('users');
            $table->timestamps();

            $table->index(['inventory_id', 'created_at']);
            $table->index(['task_id', 'created_at']);
            $table->index('performed_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_inventory_logs');
    }
}; 