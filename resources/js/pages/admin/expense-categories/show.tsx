import { Head, Link } from '@inertiajs/react';
import { StatusBadge } from '@/components/status-badge/status-badge';
import { StatusToggle } from '@/components/status-toggle';
import { Button } from '@/components/ui/button';
import {
    destroyExpenseCategory,
    type AdminExpenseCategory,
} from '@/pages/admin/expense-categories/category-form';

type Props = {
    category: AdminExpenseCategory;
    canManage: boolean;
};

export default function ExpenseCategoriesShow({
    category,
    canManage,
}: Props) {
    return (
        <>
            <Head title={category.name} />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                            Operations
                        </p>
                        <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight">
                            {category.name}
                        </h2>
                        <div className="mt-2 flex flex-wrap items-center gap-2">
                            <StatusBadge
                                label={category.status_label}
                                badge={category.status_badge}
                            />
                        </div>
                    </div>
                    <div className="flex flex-wrap items-center gap-2">
                        <StatusToggle
                            endpoint={`/admin/expense-categories/${category.id}/status`}
                            status={category.status}
                            canToggle={canManage}
                            activeLabel="Deactivate category"
                            inactiveLabel="Activate category"
                        />
                        <Button variant="outline" asChild>
                            <Link href="/admin/expense-categories">Back</Link>
                        </Button>
                        {canManage && (
                            <Button asChild>
                                <Link
                                    href={`/admin/expense-categories/${category.id}/edit`}
                                >
                                    Edit
                                </Link>
                            </Button>
                        )}
                        {canManage && category.expenses_count === 0 && (
                            <Button
                                variant="destructive"
                                type="button"
                                onClick={() =>
                                    destroyExpenseCategory(category.id)
                                }
                            >
                                Delete
                            </Button>
                        )}
                    </div>
                </div>

                <div className="grid gap-4 rounded-xl border border-border/80 bg-card p-4 md:grid-cols-2 md:p-6">
                    <Detail
                        label="Description"
                        value={category.description || '—'}
                    />
                    <Detail
                        label="Linked expenses"
                        value={String(category.expenses_count)}
                    />
                </div>
            </div>
        </>
    );
}

function Detail({ label, value }: { label: string; value: string }) {
    return (
        <div>
            <p className="text-[11px] font-bold tracking-[0.08em] text-muted-foreground uppercase">
                {label}
            </p>
            <p className="mt-1 text-sm font-medium whitespace-pre-wrap">
                {value}
            </p>
        </div>
    );
}

ExpenseCategoriesShow.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        {
            title: 'Expense Categories',
            href: '/admin/expense-categories',
        },
        { title: 'Details', href: '#' },
    ],
};
