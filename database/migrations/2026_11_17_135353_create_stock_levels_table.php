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
        Schema::create('stock_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->restrictOnDelete();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('warehouse_id');
            $table->unsignedBigInteger('quantity');
            $table->timestamp('updated_at');


            $table->foreign(['tenant_id','product_id'])->references(['tenant_id', 'id'])->on('products')->restrictOnDelete();
            $table->foreign(['tenant_id','warehouse_id'])->references(['tenant_id', 'id'])->on('warehouses')->restrictOnDelete();

            $table->unique([
               'tenant_id',
               'product_id',
               'warehouse_id'
            ],'stock_levels_unique');

            $table->index([
                'tenant_id',
                'warehouse_id',
                'product_id'
            ],'stock_levels_warehouse_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_levels');
    }
};
