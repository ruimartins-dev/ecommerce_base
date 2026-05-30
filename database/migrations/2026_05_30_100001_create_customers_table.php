<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Note: `default_address_id` is created here as a nullable indexed column
     * without a foreign key to avoid a circular dependency with `addresses`
     * (which references `customers`). The FK is added in a later migration.
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();
            $table->string('company_name');
            $table->string('vat_number')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_blocked')->default(false);
            $table->unsignedBigInteger('default_address_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('vat_number');
            $table->index('default_address_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};

