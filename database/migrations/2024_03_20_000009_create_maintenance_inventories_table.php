<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_inventories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku', 50)->unique();
            $table->text('description')->nullable();
            $table->decimal('quantity', 10, 2);
            $table->string('unit', 20);
            $table->decimal('min_quantity', 10, 2);
            $table->decimal('reorder_point', 10, 2);
            $table->string('category')->nullable();
            $table->string('location')->nullable();
            $table->json('supplier_info')->nullable();
            $table->timestamp('last_ordered_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('sku');
            $table->index('category');
            $table->index('location');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_inventories');
    }
}; 