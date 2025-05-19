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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->string('type');
            $table->integer('floor');
            $table->text('description')->nullable();
            $table->decimal('price_per_night', 10, 2);
            $table->decimal('base_price', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->integer('capacity');
            $table->boolean('is_available')->default(true);
            $table->boolean('needs_maintenance')->default(false);
            $table->boolean('is_maintenance')->default(false);
            $table->string('status')->default('active');
            $table->boolean('is_blocked')->default(false);
            $table->string('block_reason')->nullable();
            $table->timestamp('block_until')->nullable();
            $table->boolean('allow_waitlist')->default(false);
            $table->integer('max_overbooking')->default(0);
            $table->json('amenities')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
