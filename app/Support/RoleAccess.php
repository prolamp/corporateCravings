<?php

namespace App\Support;

use App\Models\User;
use App\Support\Enums\UserRole;
use Illuminate\Support\Collection;

class RoleAccess
{
    /**
     * Resolve the primary role for a user.
     */
    public static function roleOf(?User $user): ?UserRole
    {
        return $user?->primaryRole();
    }

    public static function roleNameOf(?User $user): ?string
    {
        return $user?->primaryRoleName();
    }

    public static function roleLabelOf(?User $user): ?string
    {
        return $user?->primaryRoleLabel();
    }

    public static function isRole(?User $user, UserRole|string $role): bool
    {
        return $user?->hasRoleEnum($role) ?? false;
    }

    /**
     * @return list<string>
     */
    public static function allPermissionNames(): array
    {
        return config('permissions.permissions', []);
    }

    /**
     * @return list<string>
     */
    public static function permissionsForRole(UserRole|string $role): array
    {
        $key = $role instanceof UserRole ? $role->value : $role;

        if ($key === UserRole::SuperAdmin->value) {
            return self::allPermissionNames();
        }

        return config("permissions.roles.{$key}", []);
    }

    /**
     * @return list<string>
     */
    public static function dashboardWidgetsFor(User $user): array
    {
        $role = $user->getRoleNames()->first();

        if (! $role) {
            return [];
        }

        return config("permissions.dashboard_widgets.{$role}", []);
    }

    /**
     * Roles the actor is allowed to assign when creating/updating users.
     *
     * @return list<UserRole>
     */
    public static function creatableRoles(User $actor): array
    {
        return array_values(array_filter(
            UserRole::cases(),
            fn (UserRole $role) => self::canCreateRole($actor, $role),
        ));
    }

    public static function canCreateRole(User $actor, UserRole $target): bool
    {
        return match ($target) {
            UserRole::SuperAdmin => false,
            UserRole::Admin => $actor->can('users.create_admin'),
            UserRole::Manager => $actor->can('users.create_manager'),
            UserRole::Assistant => $actor->can('users.create_assistant'),
            UserRole::Subscriber => $actor->can('users.create_subscriber'),
            UserRole::Customer => $actor->can('users.create_customer'),
        };
    }

    public static function canViewEarnings(User $user): bool
    {
        return $user->can('reports.earnings');
    }

    public static function canViewProfitLoss(User $user): bool
    {
        return $user->can('reports.profit_loss');
    }

    public static function canViewPayments(User $user): bool
    {
        return $user->can('payments.view');
    }

    public static function primaryDashboardRoute(User $user): string
    {
        if ($user->hasAnyRole([
            UserRole::SuperAdmin->value,
            UserRole::Admin->value,
            UserRole::Manager->value,
            UserRole::Assistant->value,
        ])) {
            return 'dashboard';
        }

        if ($user->hasRole(UserRole::Subscriber->value)) {
            return 'subscriber.dashboard';
        }

        return 'customer.dashboard';
    }

    /**
     * @return Collection<string, string>
     */
    public static function permissionLabels(): Collection
    {
        return collect(self::allPermissionNames())
            ->mapWithKeys(fn (string $name) => [$name => str_replace(['.', '_'], [' / ', ' '], $name)]);
    }
}
