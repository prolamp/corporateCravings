<?php

namespace App\Support\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case Admin = 'admin';
    case Manager = 'manager';
    case Assistant = 'assistant';
    case Subscriber = 'subscriber';
    case Customer = 'customer';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Admin => 'Admin',
            self::Manager => 'Manager',
            self::Assistant => 'Assistant',
            self::Subscriber => 'Subscriber',
            self::Customer => 'Customer',
        };
    }

    public function isManagement(): bool
    {
        return in_array($this, [
            self::SuperAdmin,
            self::Admin,
            self::Manager,
            self::Assistant,
        ], true);
    }

    /**
     * Higher number = higher privilege. Used to hide above-level users in admin lists.
     */
    public function level(): int
    {
        return match ($this) {
            self::SuperAdmin => 50,
            self::Admin => 40,
            self::Manager => 30,
            self::Assistant => 20,
            self::Subscriber => 10,
            self::Customer => 0,
        };
    }

    public function isAbove(self $other): bool
    {
        return $this->level() > $other->level();
    }

    /**
     * Roles at or below this role (inclusive), highest first.
     *
     * @return list<self>
     */
    public function atOrBelow(): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $role) => $role->level() <= $this->level(),
        ));
    }

    /**
     * Role names strictly above this role.
     *
     * @return list<string>
     */
    public function aboveRoleNames(): array
    {
        return array_values(array_map(
            fn (self $role) => $role->value,
            array_filter(
                self::cases(),
                fn (self $role) => $role->isAbove($this),
            ),
        ));
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
