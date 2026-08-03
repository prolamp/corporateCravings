<?php

namespace App\Support\Enums;

/**
 * A dish variation is either a single serving (the only thing the cart
 * accepts) or a bulk quantity that is quoted over WhatsApp.
 */
enum VariationType: string
{
    case Single = 'single';
    case Bulk = 'bulk';

    public function label(): string
    {
        return match ($this) {
            self::Single => 'Single Serving',
            self::Bulk => 'Bulk',
        };
    }

    /**
     * Only single servings may be added to the website cart.
     */
    public function isCartable(): bool
    {
        return $this === self::Single;
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
