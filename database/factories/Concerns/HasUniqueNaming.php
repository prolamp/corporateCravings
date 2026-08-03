<?php

namespace Database\Factories\Concerns;

use Illuminate\Support\Str;

/**
 * Builds readable names that still satisfy the unique constraints on
 * catalog-style tables (categories, dishes, addons, packages, ...).
 */
trait HasUniqueNaming
{
    protected function uniqueName(int $words = 2): string
    {
        $parts = [];

        for ($i = 0; $i < $words; $i++) {
            $parts[] = fake()->word();
        }

        return Str::title(implode(' ', $parts)).' '.Str::upper(Str::random(4));
    }
}
