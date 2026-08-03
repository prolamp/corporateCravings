import { Head, Link, router } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { Pagination } from '@/components/pagination';
import { StatusBadge } from '@/components/status-badge/status-badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type {
    AdminExpense,
    SelectOption,
    StatusOption,
} from '@/pages/admin/expenses/expense-form';

type Paginator = {
    data: AdminExpense[];
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
    expenses: Paginator;
    filters: {
        search: string | null;
        status: string | null;
        expense_category_id: number | null;
        date_from: string | null;
        date_to: string | null;
    };
    statusOptions: StatusOption[];
    categoryOptions: SelectOption[];
    canCreate: boolean;
    canManage: boolean;
    canViewTotals: boolean;
    totals: { filtered: number; filtered_formatted: string } | null;
};

export default function ExpensesIndex({
    expenses,
    filters,
    statusOptions,
    categoryOptions,
    canCreate,
    canManage,
    canViewTotals,
    totals,
}: Props) {
    function applyFilters(next: Partial<Props['filters']>) {
        router.get(
            '/admin/expenses',
            {
                search: next.search ?? filters.search ?? '',
                status: next.status ?? filters.status ?? '',
                expense_category_id:
                    next.expense_category_id ??
                    filters.expense_category_id ??
                    '',
                date_from: next.date_from ?? filters.date_from ?? '',
                date_to: next.date_to ?? filters.date_to ?? '',
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
        if (filters.expense_category_id) {
            params.set(
                'expense_category_id',
                String(filters.expense_category_id),
            );
        }
        if (filters.date_from) {
            params.set('date_from', filters.date_from);
        }
        if (filters.date_to) {
            params.set('date_to', filters.date_to);
        }
        if (page > 1) {
            params.set('page', String(page));
        }
        const query = params.toString();

        return query ? `/admin/expenses?${query}` : '/admin/expenses';
    }

    return (
        <>
            <Head title="Expenses" />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                            Operations
                        </p>
                        <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight md:text-3xl">
                            Expenses
                        </h2>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Track groceries and ops spend. Grocery unit prices
                            feed the dish cost calculator.
                        </p>
                    </div>
                    {canCreate && (
                        <Button asChild>
                            <Link href="/admin/expenses/create">
                                <Plus className="size-4" />
                                Add expense
                            </Link>
                        </Button>
                    )}
                </div>

                {canViewTotals && totals && (
                    <div className="rounded-xl border border-border/80 bg-card px-4 py-3">
                        <p className="text-[11px] font-bold tracking-[0.08em] text-muted-foreground uppercase">
                            Filtered total
                        </p>
                        <p className="mt-1 font-display text-2xl font-semibold">
                            {totals.filtered_formatted}
                        </p>
                    </div>
                )}

                <div className="grid gap-3 rounded-xl border border-border/80 bg-card p-4 md:grid-cols-3 lg:grid-cols-6">
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
                        value={filters.expense_category_id ?? ''}
                        onChange={(e) =>
                            applyFilters({
                                expense_category_id: e.target.value
                                    ? Number(e.target.value)
                                    : null,
                            })
                        }
                    >
                        <option value="">All categories</option>
                        {categoryOptions.map((option) => (
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
                    <Input
                        type="date"
                        value={filters.date_from ?? ''}
                        onChange={(e) =>
                            applyFilters({
                                date_from: e.target.value || null,
                            })
                        }
                    />
                    <Input
                        type="date"
                        value={filters.date_to ?? ''}
                        onChange={(e) =>
                            applyFilters({
                                date_to: e.target.value || null,
                            })
                        }
                    />
                    <Button
                        type="button"
                        variant="outline"
                        onClick={() =>
                            router.get('/admin/expenses', {}, { replace: true })
                        }
                    >
                        Clear filters
                    </Button>
                </div>

                <div className="overflow-hidden rounded-xl border border-border/80 bg-card">
                    {expenses.data.length === 0 ? (
                        <div className="px-4 py-12 text-center text-sm text-muted-foreground">
                            No expenses match these filters.
                        </div>
                    ) : (
                        <>
                            <div className="overflow-x-auto">
                                <table className="w-full min-w-[760px] text-left text-sm">
                                    <thead className="bg-muted/40 text-[11px] font-bold tracking-wider text-muted-foreground uppercase">
                                        <tr>
                                            <th className="px-4 py-3">Date</th>
                                            <th className="px-4 py-3">
                                                Expense
                                            </th>
                                            <th className="px-4 py-3">
                                                Category
                                            </th>
                                            <th className="px-4 py-3">Qty</th>
                                            <th className="px-4 py-3">Total</th>
                                            <th className="px-4 py-3">
                                                Status
                                            </th>
                                            <th className="px-4 py-3 text-right">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-border/60">
                                        {expenses.data.map((expense) => (
                                            <tr key={expense.id}>
                                                <td className="px-4 py-3 whitespace-nowrap">
                                                    {expense.date_formatted}
                                                </td>
                                                <td className="px-4 py-3 font-medium">
                                                    {expense.title}
                                                </td>
                                                <td className="px-4 py-3">
                                                    {expense.category_name}
                                                </td>
                                                <td className="px-4 py-3">
                                                    {expense.quantity}
                                                    {expense.unit
                                                        ? ` ${expense.unit}`
                                                        : ''}
                                                </td>
                                                <td className="px-4 py-3">
                                                    {expense.total_formatted}
                                                </td>
                                                <td className="px-4 py-3">
                                                    <StatusBadge
                                                        label={
                                                            expense.status_label
                                                        }
                                                        badge={
                                                            expense.status_badge
                                                        }
                                                    />
                                                </td>
                                                <td className="px-4 py-3 text-right">
                                                    <div className="flex flex-wrap items-center justify-end gap-2">
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            asChild
                                                        >
                                                            <Link
                                                                href={`/admin/expenses/${expense.id}`}
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
                                                                    href={`/admin/expenses/${expense.id}/edit`}
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
                                meta={expenses.meta}
                                pageUrl={pageUrl}
                            />
                        </>
                    )}
                </div>
            </div>
        </>
    );
}

ExpensesIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Expenses', href: '/admin/expenses' },
    ],
};
