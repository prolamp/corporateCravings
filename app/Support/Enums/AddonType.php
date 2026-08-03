<?php

namespace App\Support\Enums;

enum AddonType: string
{
    case Sauce = 'sauce';
    case Drink = 'drink';
    case Extra = 'extra';

    public function label(): string
    {
        return match ($this) {
            self::Sauce => 'Sauce',
            self::Drink => 'Drink',
            self::Extra => 'Extra',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
