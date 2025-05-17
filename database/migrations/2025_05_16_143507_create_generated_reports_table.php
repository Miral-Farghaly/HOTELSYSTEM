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
        Schema::create('generated_reports', function (Blueprint $table) {
            $table->id('generated_id');
            $table->unsignedBigInteger('report_id');
            $table->unsignedBigInteger('branch_id');
            $table->date('period_start');
            $table->date('period_end');
            $table->string('file_path');
            $table->timestamp('generated_at')->useCurrent();

            $table->foreign('report_id')->references('report_id')->on('reports')->onDelete('cascade');
          
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generated_reports');
    }
};
