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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->restrictOnDelete();
            $table->string('sku');
            $table->string('name');
            $table->decimal('unit_price',15,2)->nullable();
            $table->timestamp('created_at');

            $table->unique([
                'tenant_id',
                'sku'
            ],'products_tenant_id_sku_unique');

            $table->unique([
                'tenant_id',
                'id'
            ], 'products_tenant_id_id_unique');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
