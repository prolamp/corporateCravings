<?php

namespace App\Support\Enums;

enum OrderType: string
{
    case Individual = 'individual';
    case Event = 'event';
    case Subscription = 'subscription';

    public function label(): string
    {
        return match ($this) {
            self::Individual => 'Individual',
            self::Event => 'Event / Bulk',
            self::Subscription => 'Subscription',
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
