<?php

namespace App\Support\Enums;

enum OrderChannel: string
{
    case Web = 'web';
    case MobileApp = 'mobile_app';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Web => 'Website',
            self::MobileApp => 'Mobile App',
            self::Admin => 'Admin Panel',
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
