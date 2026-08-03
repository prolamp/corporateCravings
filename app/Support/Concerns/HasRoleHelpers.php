<?php

namespace App\Support\Concerns;

use App\Support\Enums\UserRole;
use App\Support\RoleAccess;

/**
 * Reusable role helpers for the User model (Spatie roles).
 */
trait HasRoleHelpers
{
    public function primaryRoleName(): ?string
    {
        return $this->getRoleNames()->first();
    }

    public function primaryRole(): ?UserRole
    {
        $name = $this->primaryRoleName();

        return $name ? UserRole::tryFrom($name) : null;
    }

    public function primaryRoleLabel(): ?string
    {
        return $this->primaryRole()?->label();
    }

    /**
     * @return list<string>
     */
    public function roleNames(): array
    {
        return array_values(array_map(strval(...), $this->getRoleNames()->all()));
    }

    /**
     * @return list<UserRole>
     */
    public function roleEnums(): array
    {
        return array_values(array_filter(array_map(
            fn (string $name) => UserRole::tryFrom($name),
            $this->roleNames(),
        )));
    }

    public function hasRoleEnum(UserRole|string $role): bool
    {
        $value = $role instanceof UserRole ? $role->value : $role;

        return $this->hasRole($value);
    }

    public function hasAnyRoleEnum(UserRole|string ...$roles): bool
    {
        $values = collect($roles)
            ->map(fn (UserRole|string $role) => $role instanceof UserRole ? $role->value : $role)
            ->all();

        return $this->hasAnyRole($values);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRoleEnum(UserRole::SuperAdmin);
    }

    public function isAdmin(): bool
    {
        return $this->hasRoleEnum(UserRole::Admin);
    }

    public function isManager(): bool
    {
        return $this->hasRoleEnum(UserRole::Manager);
    }

    public function isAssistant(): bool
    {
        return $this->hasRoleEnum(UserRole::Assistant);
    }

    public function isSubscriber(): bool
    {
        return $this->hasRoleEnum(UserRole::Subscriber);
    }

    public function isCustomer(): bool
    {
        return $this->hasRoleEnum(UserRole::Customer);
    }

    public function isManagement(): bool
    {
        return $this->hasAnyRoleEnum(
            UserRole::SuperAdmin,
            UserRole::Admin,
            UserRole::Manager,
            UserRole::Assistant,
        );
    }

    public function isPortalUser(): bool
    {
        return $this->isSubscriber() || $this->isCustomer();
    }

    /**
     * @return list<string>
     */
    public function permissionNames(): array
    {
        return array_values(array_map(strval(...), $this->getAllPermissions()->pluck('name')->all()));
    }

    public function allows(string $permission): bool
    {
        return $this->can($permission);
    }

    public function denies(string $permission): bool
    {
        return ! $this->allows($permission);
    }

    /**
     * @return list<UserRole>
     */
    public function creatableRoles(): array
    {
        return RoleAccess::creatableRoles($this);
    }

    /**
     * @return list<string>
     */
    public function dashboardWidgets(): array
    {
        return RoleAccess::dashboardWidgetsFor($this);
    }

    public function dashboardRouteName(): string
    {
        return RoleAccess::primaryDashboardRoute($this);
    }

    /**
     * Compact payload for Inertia / API responses.
     *
     * @return array{
     *     role: string|null,
     *     role_label: string|null,
     *     roles: list<string>,
     *     is_management: bool,
     *     is_super_admin: bool,
     *     is_admin: bool,
     *     is_manager: bool,
     *     is_assistant: bool,
     *     is_subscriber: bool,
     *     is_customer: bool,
     *     permissions: list<string>,
     *     creatable_roles: list<array{value: string, label: string}>,
     *     can_view_earnings: bool,
     *     can_view_profit_loss: bool,
     *     can_view_payments: bool,
     *     dashboard_route: string
     * }
     */
    public function rolePayload(): array
    {
        return [
            'role' => $this->primaryRoleName(),
            'role_label' => $this->primaryRoleLabel(),
            'roles' => $this->roleNames(),
            'is_management' => $this->isManagement(),
            'is_super_admin' => $this->isSuperAdmin(),
            'is_admin' => $this->isAdmin(),
            'is_manager' => $this->isManager(),
            'is_assistant' => $this->isAssistant(),
            'is_subscriber' => $this->isSubscriber(),
            'is_customer' => $this->isCustomer(),
            'permissions' => $this->permissionNames(),
            'creatable_roles' => array_map(
                fn (UserRole $role) => [
                    'value' => $role->value,
                    'label' => $role->label(),
                ],
                $this->creatableRoles(),
            ),
            'can_view_earnings' => RoleAccess::canViewEarnings($this),
            'can_view_profit_loss' => RoleAccess::canViewProfitLoss($this),
            'can_view_payments' => RoleAccess::canViewPayments($this),
            'dashboard_route' => $this->dashboardRouteName(),
        ];
    }
}
