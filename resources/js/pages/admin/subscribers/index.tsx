import { Head, Link, router } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { Pagination } from '@/components/pagination';
import { StatusBadge } from '@/components/status-badge/status-badge';
import { StatusToggle } from '@/components/status-toggle';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type {
    AdminSubscriber,
    StatusOption,
} from '@/pages/admin/subscribers/subscriber-form';

type Paginator = {
    data: AdminSubscriber[];
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
    subscribers: Paginator;
    filters: {
        search: string | null;
        status: string | null;
    };
    statusOptions: StatusOption[];
    canCreate: boolean;
    canManage: boolean;
};

export default function SubscribersIndex({
    subscribers,
    filters,
    statusOptions,
    canCreate,
    canManage,
}: Props) {
    function applyFilters(next: Partial<Props['filters']>) {
        router.get(
            '/admin/subscribers',
            {
                search: next.search ?? filters.search ?? '',
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
        if (filters.status) {
            params.set('status', filters.status);
        }
        if (page > 1) {
            params.set('page', String(page));
        }
        const query = params.toString();

        return query ? `/admin/subscribers?${query}` : '/admin/subscribers';
    }

    return (
        <>
            <Head title="Subscribers" />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                            Packages
                        </p>
                        <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight md:text-3xl">
                            Corporate subscribers
                        </h2>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Company accounts. Contract pricing is staff-only.
                        </p>
                    </div>
                    {canCreate && (
                        <Button asChild>
                            <Link href="/admin/subscribers/create">
                                <Plus className="size-4" />
                                Add subscriber
                            </Link>
                        </Button>
                    )}
                </div>

                <div className="grid gap-3 rounded-xl border border-border/80 bg-card p-4 md:grid-cols-3">
                    <Input
                        placeholder="Search company, contact, phone…"
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
                            router.get(
                                '/admin/subscribers',
                                {},
                                { replace: true },
                            )
                        }
                    >
                        Clear filters
                    </Button>
                </div>

                <div className="overflow-hidden rounded-xl border border-border/80 bg-card">
                    {subscribers.data.length === 0 ? (
                        <div className="px-4 py-12 text-center text-sm text-muted-foreground">
                            No subscribers match these filters.
                        </div>
                    ) : (
                        <>
                            <div className="overflow-x-auto">
                                <table className="w-full min-w-[800px] text-left text-sm">
                                    <thead className="bg-muted/40 text-[11px] font-bold tracking-wider text-muted-foreground uppercase">
                                        <tr>
                                            <th className="px-4 py-3">
                                                Company
                                            </th>
                                            <th className="px-4 py-3">
                                                Package
                                            </th>
                                            <th className="px-4 py-3">
                                                Contract total
                                            </th>
                                            <th className="px-4 py-3">
                                                Status
                                            </th>
                                            <th className="px-4 py-3 text-right">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-border/60">
                                        {subscribers.data.map((row) => (
                                            <tr key={row.id}>
                                                <td className="px-4 py-3">
                                                    <div className="flex items-center gap-3">
                                                        {row.image_url ? (
                                                            <img
                                                                src={
                                                                    row.image_url
                                                                }
                                                                alt=""
                                                                className="size-10 rounded-lg object-cover"
                                                            />
                                                        ) : (
                                                            <div className="flex size-10 items-center justify-center rounded-lg bg-muted text-xs font-bold text-muted-foreground">
                                                                {(
                                                                    row.company_name ||
                                                                    row.name
                                                                )
                                                                    .slice(0, 1)
                                                                    .toUpperCase()}
                                                            </div>
                                                        )}
                                                        <div className="min-w-0">
                                                            <p className="truncate font-medium">
                                                                {row.company_name ||
                                                                    row.name}
                                                            </p>
                                                            <p className="truncate text-xs text-muted-foreground">
                                                                {row.name}
                                                                {row.phone
                                                                    ? ` · ${row.phone}`
                                                                    : ''}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td className="px-4 py-3">
                                                    {row.contract
                                                        ?.package_title ?? '—'}
                                                </td>
                                                <td className="px-4 py-3">
                                                    <span className="font-medium">
                                                        {row.contract
                                                            ?.agreed_total_formatted ??
                                                            '—'}
                                                    </span>
                                                    {row.contract && (
                                                        <span className="ml-2 text-[10px] font-bold tracking-wider text-muted-foreground uppercase">
                                                            Staff only
                                                        </span>
                                                    )}
                                                </td>
                                                <td className="px-4 py-3">
                                                    <StatusBadge
                                                        label={
                                                            row.status_label
                                                        }
                                                        badge={
                                                            row.status_badge
                                                        }
                                                    />
                                                </td>
                                                <td className="px-4 py-3 text-right">
                                                    <div className="flex flex-wrap items-center justify-end gap-2">
                                                        <StatusToggle
                                                            endpoint={`/admin/subscribers/${row.id}/status`}
                                                            status={row.status}
                                                            canToggle={
                                                                canManage &&
                                                                (row.can_update ??
                                                                    true)
                                                            }
                                                            activeLabel="Deactivate subscriber"
                                                            inactiveLabel="Activate subscriber"
                                                        />
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            asChild
                                                        >
                                                            <Link
                                                                href={`/admin/subscribers/${row.id}`}
                                                            >
                                                                View
                                                            </Link>
                                                        </Button>
                                                        {canManage &&
                                                            row.can_update !==
                                                                false && (
                                                                <Button
                                                                    variant="outline"
                                                                    size="sm"
                                                                    asChild
                                                                >
                                                                    <Link
                                                                        href={`/admin/subscribers/${row.id}/edit`}
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
                            <Pagination
                                meta={subscribers.meta}
                                pageUrl={pageUrl}
                            />
                        </>
                    )}
                </div>
            </div>
        </>
    );
}

SubscribersIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Subscribers', href: '/admin/subscribers' },
    ],
};
