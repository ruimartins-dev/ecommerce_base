<?php

declare(strict_types=1);

namespace App\Support\Database;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Helper to add portable DB-level CHECK constraints from migrations.
 *
 * SQLite cannot add CHECK constraints through ALTER TABLE, so on that
 * driver this is a no-op and integrity is enforced via column types
 * (unsigned integers) and the model layer instead. On MySQL / MariaDB /
 * PostgreSQL a real CHECK constraint is created.
 */
final class CheckConstraint
{
    /**
     * Add a CHECK constraint when the underlying driver supports it.
     */
    public static function add(string $table, string $name, string $expression): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (! in_array($driver, ['mysql', 'mariadb', 'pgsql'], true)) {
            return;
        }

        DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$name} CHECK ({$expression})");
    }
}

