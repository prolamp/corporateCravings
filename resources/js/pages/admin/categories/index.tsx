import { Head, Link, router } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { Pagination } from '@/components/pagination';
import { StatusBadge } from '@/components/status-badge/status-badge';
import { StatusToggle } from '@/components/status-toggle';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type {
    AdminCategory,
    StatusOption,
} from '@/pages/admin/categories/category-form';

type Paginator = {
    data: AdminCategory[];
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
    categories: Paginator;
    filters: {
        search: string | null;
        status: string | null;
    };
    statusOptions: StatusOption[];
    canManage: boolean;
};

export default function CategoriesIndex({
    categories,
    filters,
    statusOptions,
    canManage,
}: Props) {
    function applyFilters(next: Partial<Props['filters']>) {
        router.get(
            '/admin/categories',
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

        return query ? `/admin/categories?${query}` : '/admin/categories';
    }

    return (
        <>
            <Head title="Categories" />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                            Catalog
                        </p>
                        <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight md:text-3xl">
                            Categories
                        </h2>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Organize dishes. Images are compressed and cleaned
                            up on replace or delete.
                        </p>
                    </div>
                    {canManage && (
                        <Button asChild>
                            <Link href="/admin/categories/create">
                                <Plus className="size-4" />
                                Add category
                            </Link>
                        </Button>
                    )}
                </div>

                <div className="grid gap-3 rounded-xl border border-border/80 bg-card p-4 md:grid-cols-3">
                    <Input
                        placeholder="Search name or slug…"
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
                                '/admin/categories',
                                {},
                                { replace: true },
                            )
                        }
                    >
                        Clear filters
                    </Button>
                </div>

                <div className="overflow-hidden rounded-xl border border-border/80 bg-card">
                    {categories.data.length === 0 ? (
                        <div className="px-4 py-12 text-center text-sm text-muted-foreground">
                            No categories match these filters.
                        </div>
                    ) : (
                        <>
                            <div className="overflow-x-auto">
                                <table className="w-full min-w-[720px] text-left text-sm">
                                    <thead className="bg-muted/40 text-[11px] font-bold tracking-wider text-muted-foreground uppercase">
                                        <tr>
                                            <th className="px-4 py-3">
                                                Category
                                            </th>
                                            <th className="px-4 py-3">
                                                Status
                                            </th>
                                            <th className="px-4 py-3">
                                                Dishes
                                            </th>
                                            <th className="px-4 py-3">Sort</th>
                                            <th className="px-4 py-3 text-right">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-border/60">
                                        {categories.data.map((category) => (
                                            <tr key={category.id}>
                                                <td className="px-4 py-3">
                                                    <div className="flex items-center gap-3">
                                                        {category.image_url ? (
                                                            <img
                                                                src={
                                                                    category.image_url
                                                                }
                                                                alt=""
                                                                className="size-10 rounded-lg object-cover"
                                                            />
                                                        ) : (
                                                            <div className="flex size-10 items-center justify-center rounded-lg bg-muted text-xs font-bold text-muted-foreground">
                                                                {category.name
                                                                    .slice(0, 1)
                                                                    .toUpperCase()}
                                                            </div>
                                                        )}
                                                        <div className="min-w-0">
                                                            <p className="truncate font-medium">
                                                                {category.name}
                                                            </p>
                                                            <p className="truncate text-xs text-muted-foreground">
                                                                {category.slug}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td className="px-4 py-3">
                                                    <StatusBadge
                                                        label={
                                                            category.status_label
                                                        }
                                                        badge={
                                                            category.status_badge
                                                        }
                                                    />
                                                </td>
                                                <td className="px-4 py-3">
                                                    {category.dishes_count}
                                                </td>
                                                <td className="px-4 py-3">
                                                    {category.sort_order}
                                                </td>
                                                <td className="px-4 py-3 text-right">
                                                    <div className="flex flex-wrap items-center justify-end gap-2">
                                                        <StatusToggle
                                                            endpoint={`/admin/categories/${category.id}/status`}
                                                            status={
                                                                category.status
                                                            }
                                                            canToggle={
                                                                canManage
                                                            }
                                                            activeLabel="Deactivate category"
                                                            inactiveLabel="Activate category"
                                                        />
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            asChild
                                                        >
                                                            <Link
                                                                href={`/admin/categories/${category.id}`}
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
                                                                    href={`/admin/categories/${category.id}/edit`}
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
                                meta={categories.meta}
                                pageUrl={pageUrl}
                            />
                        </>
                    )}
                </div>
            </div>
        </>
    );
}

CategoriesIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Categories', href: '/admin/categories' },
    ],
};
