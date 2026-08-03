import type { AppRole, User } from '@/types';

export function getUserRoles(user?: User | null): string[] {
    return user?.roles ?? [];
}

export function getPrimaryRole(user?: User | null): string | null {
    return user?.role ?? getUserRoles(user)[0] ?? null;
}

export function userHasRole(
    user: User | null | undefined,
    role: AppRole | string,
): boolean {
    return getUserRoles(user).includes(role);
}

export function userHasAnyRole(
    user: User | null | undefined,
    roles: Array<AppRole | string>,
): boolean {
    return roles.some((role) => userHasRole(user, role));
}

export function userCan(
    user: User | null | undefined,
    permission: string,
): boolean {
    return (user?.permissions ?? []).includes(permission);
}

export function isManagementUser(user?: User | null): boolean {
    return Boolean(user?.is_management) || userHasAnyRole(user, [
        'super_admin',
        'admin',
        'manager',
        'assistant',
    ]);
}

export function isSuperAdmin(user?: User | null): boolean {
    return Boolean(user?.is_super_admin) || userHasRole(user, 'super_admin');
}

export function isSubscriber(user?: User | null): boolean {
    return Boolean(user?.is_subscriber) || userHasRole(user, 'subscriber');
}

export function isCustomer(user?: User | null): boolean {
    return Boolean(user?.is_customer) || userHasRole(user, 'customer');
}
