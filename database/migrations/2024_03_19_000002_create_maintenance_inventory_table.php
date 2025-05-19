<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('maintenance_inventory', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->unique();
            $table->integer('quantity');
            $table->string('unit');
            $table->integer('minimum_quantity');
            $table->integer('reorder_point');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('maintenance_inventory');
    }
}; 