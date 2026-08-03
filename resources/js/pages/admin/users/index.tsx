import { Head, Link, router } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { Pagination } from '@/components/pagination';
import { StatusBadge } from '@/components/status-badge/status-badge';
import { UserStatusToggle } from '@/components/user-status-toggle';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type {
    AdminUser,
    RoleOption,
    StatusOption,
} from '@/pages/admin/users/user-form';

type Paginator = {
    data: AdminUser[];
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number | null;
        to: number | null;
    };
};

type Props = {
    users: Paginator;
    filters: {
        search: string | null;
        role: string | null;
        status: string | null;
    };
    roleOptions: RoleOption[];
    statusOptions: StatusOption[];
    canCreate: boolean;
};

export default function UsersIndex({
    users,
    filters,
    roleOptions,
    statusOptions,
    canCreate,
}: Props) {
    function applyFilters(next: Partial<Props['filters']>) {
        router.get(
            '/admin/users',
            {
                search: next.search ?? filters.search ?? '',
                role: next.role ?? filters.role ?? '',
                status: next.status ?? filters.status ?? '',
            },
            { preserveState: true, replace: true },
        );
    }

    function pageUrl(page: number): string {
        const params = new URLSearchParams();

        if (filters.search) {
            params.set('search', filters.search);
        }
        if (filters.role) {
            params.set('role', filters.role);
        }
        if (filters.status) {
            params.set('status', filters.status);
        }
        if (page > 1) {
            params.set('page', String(page));
        }

        const query = params.toString();

        return query ? `/admin/users?${query}` : '/admin/users';
    }

    return (
        <>
            <Head title="Users" />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                            People
                        </p>
                        <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight md:text-3xl">
                            Users &amp; roles
                        </h2>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Manage staff, subscribers, and customers. Role
                            options respect your permissions.
                        </p>
                    </div>
                    {canCreate && (
                        <Button asChild>
                            <Link href="/admin/users/create">
                                <Plus className="size-4" />
                                Add user
                            </Link>
                        </Button>
                    )}
                </div>

                <div className="grid gap-3 rounded-xl border border-border/80 bg-card p-4 md:grid-cols-4">
                    <Input
                        placeholder="Search name, phone, email…"
                        defaultValue={filters.search ?? ''}
                        onKeyDown={(e) => {
                            if (e.key === 'Enter') {
                                applyFilters({
                                    search: (
                                        e.target as HTMLInputElement
                                    ).value,
                                });
                            }
                        }}
                    />
                    <select
                        className="border-input h-9 rounded-md border bg-background px-3 text-sm"
                        value={filters.role ?? ''}
                        onChange={(e) =>
                            applyFilters({ role: e.target.value || null })
                        }
                    >
                        <option value="">All roles</option>
                        {roleOptions.map((option) => (
                            <option key={option.value} value={option.value}>
                                {option.label}
                            </option>
                        ))}
                    </select>
                    <select
                        className="border-input h-9 rounded-md border bg-background px-3 text-sm"
                        value={filters.status ?? ''}
                        onChange={(e) =>
                            applyFilters({ status: e.target.value || null })
                        }
                    >
                        <option value="">All statuses</option>
                        {statusOptions.map((option) => (
                            <option key={option.value} value={option.value}>
                                {option.label}
                            </option>
                        ))}
                    </select>
                    <Button
                        type="button"
                        variant="outline"
                        onClick={() =>
                            router.get('/admin/users', {}, { replace: true })
                        }
                    >
                        Clear filters
                    </Button>
                </div>

                <div className="overflow-hidden rounded-xl border border-border/80 bg-card">
                    {users.data.length === 0 ? (
                        <div className="px-4 py-12 text-center text-sm text-muted-foreground">
                            No users match these filters.
                        </div>
                    ) : (
                        <>
                            <div className="overflow-x-auto">
                                <table className="w-full min-w-[720px] text-left text-sm">
                                    <thead className="bg-muted/40 text-[11px] font-bold tracking-wider text-muted-foreground uppercase">
                                        <tr>
                                            <th className="px-4 py-3">User</th>
                                            <th className="px-4 py-3">Role</th>
                                            <th className="px-4 py-3">
                                                Status
                                            </th>
                                            <th className="px-4 py-3">Phone</th>
                                            <th className="px-4 py-3 text-right">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-border/60">
                                        {users.data.map((user) => (
                                            <tr key={user.id}>
                                                <td className="px-4 py-3">
                                                    <div className="flex items-center gap-3">
                                                        {user.image_url ? (
                                                            <img
                                                                src={
                                                                    user.image_url
                                                                }
                                                                alt=""
                                                                className="size-9 rounded-full object-cover"
                                                            />
                                                        ) : (
                                                            <div className="flex size-9 items-center justify-center rounded-full bg-primary-container/40 text-xs font-bold text-on-primary-container">
                                                                {user.name
                                                                    .slice(0, 1)
                                                                    .toUpperCase()}
                                                            </div>
                                                        )}
                                                        <div className="min-w-0">
                                                            <p className="truncate font-medium">
                                                                {user.name}
                                                            </p>
                                                            <p className="truncate text-xs text-muted-foreground">
                                                                {user.email ||
                                                                    user.company_name ||
                                                                    '—'}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td className="px-4 py-3">
                                                    {user.role_label ?? '—'}
                                                </td>
                                                <td className="px-4 py-3">
                                                    <StatusBadge
                                                        label={
                                                            user.status_label
                                                        }
                                                        badge={
                                                            user.status_badge
                                                        }
                                                    />
                                                </td>
                                                <td className="px-4 py-3">
                                                    {user.phone ?? '—'}
                                                </td>
                                                <td className="px-4 py-3 text-right">
                                                    <div className="flex flex-wrap items-center justify-end gap-2">
                                                        <UserStatusToggle
                                                            userId={user.id}
                                                            status={user.status}
                                                            canToggle={Boolean(
                                                                user.can_update,
                                                            )}
                                                        />
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            asChild
                                                        >
                                                            <Link
                                                                href={`/admin/users/${user.id}`}
                                                            >
                                                                View
                                                            </Link>
                                                        </Button>
                                                        {user.can_update && (
                                                            <Button
                                                                variant="outline"
                                                                size="sm"
                                                                asChild
                                                            >
                                                                <Link
                                                                    href={`/admin/users/${user.id}/edit`}
                                                                >
                                                                    Edit
                                                                </Link>
                                                            </Button>
                                                        )}
                                                    </div>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>

                            <Pagination meta={users.meta} pageUrl={pageUrl} />
                        </>
                    )}
                </div>
            </div>
        </>
    );
}

UsersIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Users', href: '/admin/users' },
    ],
};
