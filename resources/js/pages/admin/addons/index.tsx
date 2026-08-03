import { Head, Link, router } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { Pagination } from '@/components/pagination';
import { StatusBadge } from '@/components/status-badge/status-badge';
import { StatusToggle } from '@/components/status-toggle';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type {
    AdminAddon,
    SelectOption,
    StatusOption,
} from '@/pages/admin/addons/addon-form';

type Paginator = {
    data: AdminAddon[];
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
    addons: Paginator;
    filters: {
        search: string | null;
        status: string | null;
        type: string | null;
        scope: string | null;
    };
    typeOptions: SelectOption[];
    statusOptions: StatusOption[];
    canManage: boolean;
};

export default function AddonsIndex({
    addons,
    filters,
    typeOptions,
    statusOptions,
    canManage,
}: Props) {
    function applyFilters(next: Partial<Props['filters']>) {
        router.get(
            '/admin/addons',
            {
                search: next.search ?? filters.search ?? '',
                status: next.status ?? filters.status ?? '',
                type: next.type ?? filters.type ?? '',
                scope: next.scope ?? filters.scope ?? '',
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
        if (filters.type) {
            params.set('type', filters.type);
        }
        if (filters.scope) {
            params.set('scope', filters.scope);
        }
        if (page > 1) {
            params.set('page', String(page));
        }
        const query = params.toString();

        return query ? `/admin/addons?${query}` : '/admin/addons';
    }

    return (
        <>
            <Head title="Addons" />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                            Catalog
                        </p>
                        <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight md:text-3xl">
                            Addons
                        </h2>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Sauces, drinks, and extras for cart and orders.
                        </p>
                    </div>
                    {canManage && (
                        <Button asChild>
                            <Link href="/admin/addons/create">
                                <Plus className="size-4" />
                                Add addon
                            </Link>
                        </Button>
                    )}
                </div>

                <div className="grid gap-3 rounded-xl border border-border/80 bg-card p-4 md:grid-cols-5">
                    <Input
                        placeholder="Search title…"
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
                        value={filters.type ?? ''}
                        onChange={(e) =>
                            applyFilters({ type: e.target.value || null })
                        }
                    >
                        <option value="">All types</option>
                        {typeOptions.map((option) => (
                            <option
                                key={String(option.value)}
                                value={String(option.value)}
                            >
                                {option.label}
                            </option>
                        ))}
                    </select>
                    <select
                        className="border-input h-9 rounded-md border bg-background px-3 text-sm"
                        value={filters.scope ?? ''}
                        onChange={(e) =>
                            applyFilters({ scope: e.target.value || null })
                        }
                    >
                        <option value="">All scopes</option>
                        <option value="global">Global</option>
                        <option value="linked">Dish-linked</option>
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
                            router.get('/admin/addons', {}, { replace: true })
                        }
                    >
                        Clear filters
                    </Button>
                </div>

                <div className="overflow-hidden rounded-xl border border-border/80 bg-card">
                    {addons.data.length === 0 ? (
                        <div className="px-4 py-12 text-center text-sm text-muted-foreground">
                            No addons match these filters.
                        </div>
                    ) : (
                        <>
                            <div className="overflow-x-auto">
                                <table className="w-full min-w-[760px] text-left text-sm">
                                    <thead className="bg-muted/40 text-[11px] font-bold tracking-wider text-muted-foreground uppercase">
                                        <tr>
                                            <th className="px-4 py-3">Addon</th>
                                            <th className="px-4 py-3">Type</th>
                                            <th className="px-4 py-3">Price</th>
                                            <th className="px-4 py-3">Scope</th>
                                            <th className="px-4 py-3">
                                                Status
                                            </th>
                                            <th className="px-4 py-3 text-right">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-border/60">
                                        {addons.data.map((addon) => (
                                            <tr key={addon.id}>
                                                <td className="px-4 py-3">
                                                    <div className="flex items-center gap-3">
                                                        {addon.image_url ? (
                                                            <img
                                                                src={
                                                                    addon.image_url
                                                                }
                                                                alt=""
                                                                className="size-10 rounded-lg object-cover"
                                                            />
                                                        ) : (
                                                            <div className="flex size-10 items-center justify-center rounded-lg bg-muted text-xs font-bold text-muted-foreground">
                                                                {addon.title
                                                                    .slice(0, 1)
                                                                    .toUpperCase()}
                                                            </div>
                                                        )}
                                                        <div className="min-w-0">
                                                            <p className="truncate font-medium">
                                                                {addon.title}
                                                            </p>
                                                            <p className="truncate text-xs text-muted-foreground">
                                                                {addon.short_description ||
                                                                    addon.slug}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td className="px-4 py-3">
                                                    {addon.type_label}
                                                </td>
                                                <td className="px-4 py-3 font-medium">
                                                    {addon.price_formatted}
                                                </td>
                                                <td className="px-4 py-3">
                                                    {addon.is_global
                                                        ? 'Global'
                                                        : `${addon.dishes_count} dishes`}
                                                </td>
                                                <td className="px-4 py-3">
                                                    <StatusBadge
                                                        label={
                                                            addon.status_label
                                                        }
                                                        badge={
                                                            addon.status_badge
                                                        }
                                                    />
                                                </td>
                                                <td className="px-4 py-3 text-right">
                                                    <div className="flex flex-wrap items-center justify-end gap-2">
                                                        <StatusToggle
                                                            endpoint={`/admin/addons/${addon.id}/status`}
                                                            status={
                                                                addon.status
                                                            }
                                                            canToggle={
                                                                canManage
                                                            }
                                                            activeLabel="Deactivate addon"
                                                            inactiveLabel="Activate addon"
                                                        />
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            asChild
                                                        >
                                                            <Link
                                                                href={`/admin/addons/${addon.id}`}
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
                                                                    href={`/admin/addons/${addon.id}/edit`}
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
                                meta={addons.meta}
                                pageUrl={pageUrl}
                            />
                        </>
                    )}
                </div>
            </div>
        </>
    );
}

AddonsIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Addons', href: '/admin/addons' },
    ],
};
