<?php

namespace App\Support\Enums;

enum VariationUnit: string
{
    case Serving = 'serving';
    case Kg = 'kg';
    case Persons = 'persons';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
