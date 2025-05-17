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
        Schema::create('reservation_status_logs', function (Blueprint $table) {
        $table->id('log_id');
        $table->unsignedBigInteger('reservation_id');
        $table->enum('old_status', ['pending', 'accepted', 'rejected'])->nullable();
        $table->enum('new_status', ['pending', 'accepted', 'rejected']);
        $table->unsignedBigInteger('changed_by'); 
        $table->timestamp('changed_at')->useCurrent();

        $table->foreign('reservation_id')->references('reservation_id')->on('reservations')->onDelete('cascade');
        $table->foreign('changed_by')->references('id')->on('users')->onDelete('cascade');
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
