<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->boolean('is_blocked')->default(false)->after('is_available');
            $table->string('block_reason')->nullable()->after('is_blocked');
            $table->timestamp('block_until')->nullable()->after('block_reason');
            $table->boolean('allow_waitlist')->default(true)->after('block_until');
            $table->unsignedInteger('max_overbooking')->default(0)->after('allow_waitlist');
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn([
                'is_blocked',
                'block_reason',
                'block_until',
                'allow_waitlist',
                'max_overbooking'
            ]);
        });
    }
}; 