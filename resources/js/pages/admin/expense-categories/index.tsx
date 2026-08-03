import { Head, Link, router } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { Pagination } from '@/components/pagination';
import { StatusBadge } from '@/components/status-badge/status-badge';
import { StatusToggle } from '@/components/status-toggle';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type {
    AdminExpenseCategory,
    StatusOption,
} from '@/pages/admin/expense-categories/category-form';

type Paginator = {
    data: AdminExpenseCategory[];
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

export default function ExpenseCategoriesIndex({
    categories,
    filters,
    statusOptions,
    canManage,
}: Props) {
    function applyFilters(next: Partial<Props['filters']>) {
        router.get(
            '/admin/expense-categories',
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

        return query
            ? `/admin/expense-categories?${query}`
            : '/admin/expense-categories';
    }

    return (
        <>
            <Head title="Expense Categories" />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                            Operations
                        </p>
                        <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight md:text-3xl">
                            Expense categories
                        </h2>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Group expenses (groceries, utilities, rent, and
                            more) for reporting and the cost calculator.
                        </p>
                    </div>
                    {canManage && (
                        <Button asChild>
                            <Link href="/admin/expense-categories/create">
                                <Plus className="size-4" />
                                Add category
                            </Link>
                        </Button>
                    )}
                </div>

                <div className="grid gap-3 rounded-xl border border-border/80 bg-card p-4 md:grid-cols-3">
                    <Input
                        placeholder="Search name or description…"
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
                                '/admin/expense-categories',
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
                            No expense categories match these filters.
                        </div>
                    ) : (
                        <>
                            <div className="overflow-x-auto">
                                <table className="w-full min-w-[640px] text-left text-sm">
                                    <thead className="bg-muted/40 text-[11px] font-bold tracking-wider text-muted-foreground uppercase">
                                        <tr>
                                            <th className="px-4 py-3">
                                                Category
                                            </th>
                                            <th className="px-4 py-3">
                                                Expenses
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
                                        {categories.data.map((category) => (
                                            <tr key={category.id}>
                                                <td className="px-4 py-3">
                                                    <p className="font-medium">
                                                        {category.name}
                                                    </p>
                                                    {category.description && (
                                                        <p className="mt-0.5 text-xs text-muted-foreground">
                                                            {
                                                                category.description
                                                            }
                                                        </p>
                                                    )}
                                                </td>
                                                <td className="px-4 py-3">
                                                    {category.expenses_count}
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
                                                <td className="px-4 py-3 text-right">
                                                    <div className="flex flex-wrap items-center justify-end gap-2">
                                                        <StatusToggle
                                                            endpoint={`/admin/expense-categories/${category.id}/status`}
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
                                                                href={`/admin/expense-categories/${category.id}`}
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
                                                                    href={`/admin/expense-categories/${category.id}/edit`}
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

ExpenseCategoriesIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        {
            title: 'Expense Categories',
            href: '/admin/expense-categories',
        },
    ],
};
