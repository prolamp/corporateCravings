import { Head, Link, router } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { Pagination } from '@/components/pagination';
import { StatusBadge } from '@/components/status-badge/status-badge';
import { StatusToggle } from '@/components/status-toggle';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type {
    AdminPackage,
    StatusOption,
} from '@/pages/admin/packages/package-form';

type Paginator = {
    data: AdminPackage[];
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
    packages: Paginator;
    filters: {
        search: string | null;
        status: string | null;
    };
    statusOptions: StatusOption[];
    canManage: boolean;
};

export default function PackagesIndex({
    packages,
    filters,
    statusOptions,
    canManage,
}: Props) {
    function applyFilters(next: Partial<Props['filters']>) {
        router.get(
            '/admin/packages',
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

        return query ? `/admin/packages?${query}` : '/admin/packages';
    }

    return (
        <>
            <Head title="Packages" />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                            Packages
                        </p>
                        <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight md:text-3xl">
                            Corporate packages
                        </h2>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Public price is average dish price per person after
                            discount.
                        </p>
                    </div>
                    {canManage && (
                        <Button asChild>
                            <Link href="/admin/packages/create">
                                <Plus className="size-4" />
                                Create package
                            </Link>
                        </Button>
                    )}
                </div>

                <div className="grid gap-3 rounded-xl border border-border/80 bg-card p-4 md:grid-cols-3">
                    <Input
                        placeholder="Search packages…"
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
                            router.get('/admin/packages', {}, { replace: true })
                        }
                    >
                        Clear filters
                    </Button>
                </div>

                <div className="overflow-hidden rounded-xl border border-border/80 bg-card">
                    {packages.data.length === 0 ? (
                        <div className="px-4 py-12 text-center text-sm text-muted-foreground">
                            No packages match these filters.
                        </div>
                    ) : (
                        <>
                            <div className="overflow-x-auto">
                                <table className="w-full min-w-[780px] text-left text-sm">
                                    <thead className="bg-muted/40 text-[11px] font-bold tracking-wider text-muted-foreground uppercase">
                                        <tr>
                                            <th className="px-4 py-3">
                                                Package
                                            </th>
                                            <th className="px-4 py-3">
                                                Public /pp
                                            </th>
                                            <th className="px-4 py-3">
                                                Dishes
                                            </th>
                                            <th className="px-4 py-3">
                                                Website
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
                                        {packages.data.map((pkg) => (
                                            <tr key={pkg.id}>
                                                <td className="px-4 py-3">
                                                    <div className="flex items-center gap-3">
                                                        {pkg.image_url ? (
                                                            <img
                                                                src={
                                                                    pkg.image_url
                                                                }
                                                                alt=""
                                                                className="size-12 rounded-lg object-cover"
                                                            />
                                                        ) : (
                                                            <div className="flex size-12 items-center justify-center rounded-lg bg-muted text-xs font-bold text-muted-foreground">
                                                                {pkg.title
                                                                    .slice(0, 1)
                                                                    .toUpperCase()}
                                                            </div>
                                                        )}
                                                        <div className="min-w-0">
                                                            <p className="truncate font-medium">
                                                                {pkg.title}
                                                            </p>
                                                            <p className="truncate text-xs text-muted-foreground">
                                                                {pkg.short_description ||
                                                                    pkg.slug}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td className="px-4 py-3 font-medium">
                                                    {
                                                        pkg.total_amount_per_person_formatted
                                                    }
                                                </td>
                                                <td className="px-4 py-3">
                                                    {pkg.items_count}
                                                </td>
                                                <td className="px-4 py-3">
                                                    <span
                                                        className={
                                                            pkg.show_on_website
                                                                ? 'text-sm font-medium text-foreground'
                                                                : 'text-sm text-muted-foreground'
                                                        }
                                                    >
                                                        {pkg.show_on_website
                                                            ? 'Visible'
                                                            : 'Hidden'}
                                                    </span>
                                                </td>
                                                <td className="px-4 py-3">
                                                    <StatusBadge
                                                        label={
                                                            pkg.status_label
                                                        }
                                                        badge={
                                                            pkg.status_badge
                                                        }
                                                    />
                                                </td>
                                                <td className="px-4 py-3 text-right">
                                                    <div className="flex flex-wrap items-center justify-end gap-2">
                                                        <StatusToggle
                                                            endpoint={`/admin/packages/${pkg.id}/status`}
                                                            status={pkg.status}
                                                            canToggle={
                                                                canManage
                                                            }
                                                            activeLabel="Deactivate package"
                                                            inactiveLabel="Activate package"
                                                        />
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            asChild
                                                        >
                                                            <Link
                                                                href={`/admin/packages/${pkg.id}`}
                                                            >
                                                                View
                                                            </Link>
                                                        </Button>
                                                        {canManage && (
                                                            <Button
                                                                variant="outline"
                                                                size="sm"
                                                                asChild
                                                            >
                                                                <Link
                                                                    href={`/admin/packages/${pkg.id}/edit`}
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
                                meta={packages.meta}
                                pageUrl={pageUrl}
                            />
                        </>
                    )}
                </div>
            </div>
        </>
    );
}

PackagesIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Packages', href: '/admin/packages' },
    ],
};
