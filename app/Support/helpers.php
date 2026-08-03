<?php

use App\Models\User;
use App\Support\Enums\UserRole;
use App\Support\RoleAccess;
use Illuminate\Support\Facades\Auth;

if (! function_exists('current_user')) {
    function current_user(): ?User
    {
        $user = Auth::user();

        return $user instanceof User ? $user : null;
    }
}

if (! function_exists('user_role')) {
    /**
     * Primary role enum for the given (or authenticated) user.
     */
    function user_role(?User $user = null): ?UserRole
    {
        $user ??= current_user();

        return $user?->primaryRole();
    }
}

if (! function_exists('user_role_name')) {
    function user_role_name(?User $user = null): ?string
    {
        return user_role($user)?->value;
    }
}

if (! function_exists('user_role_label')) {
    function user_role_label(?User $user = null): ?string
    {
        return user_role($user)?->label();
    }
}

if (! function_exists('user_has_role')) {
    function user_has_role(UserRole|string $role, ?User $user = null): bool
    {
        $user ??= current_user();

        return $user?->hasRoleEnum($role) ?? false;
    }
}

if (! function_exists('user_has_any_role')) {
    function user_has_any_role(UserRole|string ...$roles): bool
    {
        $user = current_user();

        if (! $user) {
            return false;
        }

        return $user->hasAnyRoleEnum(...$roles);
    }
}

if (! function_exists('user_is_management')) {
    function user_is_management(?User $user = null): bool
    {
        $user ??= current_user();

        return $user?->isManagement() ?? false;
    }
}

if (! function_exists('user_can')) {
    function user_can(string $permission, ?User $user = null): bool
    {
        $user ??= current_user();

        return $user?->allows($permission) ?? false;
    }
}

if (! function_exists('user_dashboard_route')) {
    function user_dashboard_route(?User $user = null): string
    {
        $user ??= current_user();

        return $user ? RoleAccess::primaryDashboardRoute($user) : 'login';
    }
}

if (! function_exists('role_enum')) {
    function role_enum(string $role): ?UserRole
    {
        return UserRole::tryFrom($role);
    }
}
