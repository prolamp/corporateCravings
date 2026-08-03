import { Head, Link, router } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { Pagination } from '@/components/pagination';
import { StatusBadge } from '@/components/status-badge/status-badge';
import { StatusToggle } from '@/components/status-toggle';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type {
    AdminDish,
    SelectOption,
    StatusOption,
} from '@/pages/admin/dishes/dish-form';

type Paginator = {
    data: AdminDish[];
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
    dishes: Paginator;
    filters: {
        search: string | null;
        status: string | null;
        category_id: number | null;
    };
    categoryOptions: SelectOption[];
    statusOptions: StatusOption[];
    canManage: boolean;
};

export default function DishesIndex({
    dishes,
    filters,
    categoryOptions,
    statusOptions,
    canManage,
}: Props) {
    function applyFilters(next: Partial<Props['filters']>) {
        router.get(
            '/admin/dishes',
            {
                search: next.search ?? filters.search ?? '',
                status: next.status ?? filters.status ?? '',
                category_id:
                    next.category_id ?? filters.category_id ?? '',
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
        if (filters.category_id) {
            params.set('category_id', String(filters.category_id));
        }
        if (page > 1) {
            params.set('page', String(page));
        }
        const query = params.toString();

        return query ? `/admin/dishes?${query}` : '/admin/dishes';
    }

    return (
        <>
            <Head title="Dishes" />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                            Catalog
                        </p>
                        <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight md:text-3xl">
                            Dishes
                        </h2>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Menu items with single and bulk portion prices.
                        </p>
                    </div>
                    {canManage && (
                        <Button asChild>
                            <Link href="/admin/dishes/create">
                                <Plus className="size-4" />
                                Add dish
                            </Link>
                        </Button>
                    )}
                </div>

                <div className="grid gap-3 rounded-xl border border-border/80 bg-card p-4 md:grid-cols-4">
                    <Input
                        placeholder="Search title, description, category…"
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
                        value={filters.category_id ?? ''}
                        onChange={(e) =>
                            applyFilters({
                                category_id: e.target.value
                                    ? Number(e.target.value)
                                    : null,
                            })
                        }
                    >
                        <option value="">All categories</option>
                        {categoryOptions.map((option) => (
                            <option
                                key={option.value}
                                value={String(option.value)}
                            >
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
                            router.get('/admin/dishes', {}, { replace: true })
                        }
                    >
                        Clear filters
                    </Button>
                </div>

                <div className="overflow-hidden rounded-xl border border-border/80 bg-card">
                    {dishes.data.length === 0 ? (
                        <div className="px-4 py-12 text-center text-sm text-muted-foreground">
                            No dishes match these filters.
                        </div>
                    ) : (
                        <>
                            <div className="overflow-x-auto">
                                <table className="w-full min-w-[800px] text-left text-sm">
                                    <thead className="bg-muted/40 text-[11px] font-bold tracking-wider text-muted-foreground uppercase">
                                        <tr>
                                            <th className="px-4 py-3">Dish</th>
                                            <th className="px-4 py-3">
                                                Category
                                            </th>
                                            <th className="px-4 py-3">
                                                Starting
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
                                        {dishes.data.map((dish) => (
                                            <tr key={dish.id}>
                                                <td className="px-4 py-3">
                                                    <div className="flex items-center gap-3">
                                                        {dish.image_url ? (
                                                            <img
                                                                src={
                                                                    dish.image_url
                                                                }
                                                                alt=""
                                                                className="size-12 rounded-lg object-cover"
                                                            />
                                                        ) : (
                                                            <div className="flex size-12 items-center justify-center rounded-lg bg-muted text-xs font-bold text-muted-foreground">
                                                                {dish.title
                                                                    .slice(0, 1)
                                                                    .toUpperCase()}
                                                            </div>
                                                        )}
                                                        <div className="min-w-0">
                                                            <p className="truncate font-medium">
                                                                {dish.title}
                                                            </p>
                                                            <p className="truncate text-xs text-muted-foreground">
                                                                {dish.short_description ||
                                                                    dish.slug}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td className="px-4 py-3">
                                                    {dish.category_name ?? '—'}
                                                </td>
                                                <td className="px-4 py-3 font-medium">
                                                    {dish.starting_price_formatted ??
                                                        '—'}
                                                </td>
                                                <td className="px-4 py-3">
                                                    <StatusBadge
                                                        label={
                                                            dish.status_label
                                                        }
                                                        badge={
                                                            dish.status_badge
                                                        }
                                                    />
                                                </td>
                                                <td className="px-4 py-3 text-right">
                                                    <div className="flex flex-wrap items-center justify-end gap-2">
                                                        <StatusToggle
                                                            endpoint={`/admin/dishes/${dish.id}/status`}
                                                            status={dish.status}
                                                            canToggle={
                                                                canManage
                                                            }
                                                            activeLabel="Deactivate dish"
                                                            inactiveLabel="Activate dish"
                                                        />
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            asChild
                                                        >
                                                            <Link
                                                                href={`/admin/dishes/${dish.id}`}
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
                                                                    href={`/admin/dishes/${dish.id}/edit`}
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
                                meta={dishes.meta}
                                pageUrl={pageUrl}
                            />
                        </>
                    )}
                </div>
            </div>
        </>
    );
}

DishesIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Dishes', href: '/admin/dishes' },
    ],
};
