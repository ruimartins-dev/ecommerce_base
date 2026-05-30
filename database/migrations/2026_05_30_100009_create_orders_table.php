<?php

use App\Enums\OrderStatusEnum;
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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnDelete();
            $table->foreignId('address_id')
                ->constrained('addresses')
                ->restrictOnDelete();
            $table->string('order_number')->unique();
            $table->string('status')->default(OrderStatusEnum::Pending->value);
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('placed_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });

        CheckConstraint::add('orders', 'chk_orders_subtotal_non_negative', 'subtotal >= 0');
        CheckConstraint::add('orders', 'chk_orders_tax_amount_non_negative', 'tax_amount >= 0');
        CheckConstraint::add('orders', 'chk_orders_discount_amount_non_negative', 'discount_amount >= 0');
        CheckConstraint::add('orders', 'chk_orders_total_non_negative', 'total >= 0');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

