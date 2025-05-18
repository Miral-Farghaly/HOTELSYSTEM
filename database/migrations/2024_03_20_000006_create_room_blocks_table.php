<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->onDelete('cascade');
            $table->string('reason');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedInteger('priority')->default(1);
            $table->foreignId('blocked_by')->constrained('users');
            $table->timestamps();
            
            $table->index(['room_id', 'start_date', 'end_date']);
            $table->index(['priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_blocks');
    }
}; 