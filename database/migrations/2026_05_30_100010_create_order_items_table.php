<?php

use App\Support\Database\CheckConstraint;
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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();
            // Nullable + nullOnDelete so order history survives product deletion.
            $table->foreignId('product_id')
                ->nullable()
                ->constrained('products')
                ->nullOnDelete();
            $table->string('product_name_snapshot');
            $table->string('sku_snapshot');
            $table->decimal('unit_price', 10, 2);
            $table->unsignedInteger('quantity');
            $table->decimal('line_total', 10, 2);
            $table->timestamps();
        });

        CheckConstraint::add('order_items', 'chk_order_items_quantity_positive', 'quantity > 0');
        CheckConstraint::add('order_items', 'chk_order_items_unit_price_non_negative', 'unit_price >= 0');
        CheckConstraint::add('order_items', 'chk_order_items_line_total_non_negative', 'line_total >= 0');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};

