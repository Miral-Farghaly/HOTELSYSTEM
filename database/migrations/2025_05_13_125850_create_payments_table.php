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
        Schema::create('payments', function (Blueprint $table) {
        $table->id('payment_id');
        $table->unsignedBigInteger('reservation_id');
        $table->decimal('amount', 10, 2);
        $table->enum('method', ['credit_card', 'cash']);
        $table->enum('type', ['deposit', 'balance', 'refund']);
        $table->string('gateway_txn_id')->nullable(); // transaction id 
        $table->enum('status', ['pending', 'completed', 'failed']);
        $table->timestamp('paid_at')->nullable();
        $table->timestamps();

        $table->foreign('reservation_id')->references('reservation_id')->on('reservations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
