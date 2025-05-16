<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('room_types', function (Blueprint $table) {
        $table->id('room_type_id');
        $table->string('name'); // Suite, Single, etc.
        $table->string('bed_size');
        $table->string('view_type');
        $table->decimal('base_rate', 8, 2);
        $table->text('description')->nullable();
        $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_types');
    }
};
