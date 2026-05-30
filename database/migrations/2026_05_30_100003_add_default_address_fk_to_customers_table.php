<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds the deferred foreign key for `customers.default_address_id`.
     * SQLite cannot add a foreign key via ALTER TABLE, so on that driver the
     * relationship is enforced at the model level only.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (! in_array($driver, ['mysql', 'mariadb', 'pgsql'], true)) {
            return;
        }

        Schema::table('customers', function (Blueprint $table) {
            $table->foreign('default_address_id')
                ->references('id')
                ->on('addresses')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (! in_array($driver, ['mysql', 'mariadb', 'pgsql'], true)) {
            return;
        }

        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['default_address_id']);
        });
    }
};

