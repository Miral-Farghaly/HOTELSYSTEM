<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->integer('estimated_duration')->comment('Duration in minutes');
            $table->json('required_skills')->nullable();
            $table->json('required_items')->nullable();
            $table->unsignedTinyInteger('priority_level')->default(1);
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_pattern')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('priority_level');
            $table->index('is_recurring');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_categories');
    }
}; 