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
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id('feedback_id');
            $table->unsignedBigInteger('reservation_id');
            $table->tinyInteger('rating');
            $table->text('comments')->nullable();
            $table->timestamp('submitted_at')->useCurrent();

            $table->foreign('reservation_id')->references('reservation_id')->on('reservations')->onDelete('cascade');

         });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedbacks');
    }
};
