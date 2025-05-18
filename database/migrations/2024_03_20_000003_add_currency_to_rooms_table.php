<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->decimal('base_price', 10, 2)->after('price_per_night');
            $table->string('currency')->default('USD')->after('base_price');
        });

        // Copy current price_per_night to base_price for existing records
        DB::statement('UPDATE rooms SET base_price = price_per_night');
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn(['currency', 'base_price']);
        });
    }
}; 