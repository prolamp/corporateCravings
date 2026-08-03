<?php

namespace App\Support\Concerns;

use Illuminate\Support\Facades\DB;

trait AddsCheckConstraints
{
    /**
     * MySQL 8 enforces CHECK; SQLite test driver skips these (no ALTER CHECK).
     */
    protected function check(string $table, string $name, string $expression): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$name} CHECK ({$expression})");
    }
}
