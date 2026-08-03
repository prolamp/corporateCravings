import { Head, Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { StatusBadge } from '@/components/status-badge/status-badge';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import {
    destroyExpense,
    type AdminExpense,
    type StatusOption,
} from '@/pages/admin/expenses/expense-form';

type Props = {
    expense: AdminExpense;
    statusOptions: StatusOption[];
    canManage: boolean;
};

export default function ExpensesShow({
    expense,
    statusOptions,
    canManage,
}: Props) {
    const form = useForm({ status: expense.status });

    function submitStatus(event: FormEvent) {
        event.preventDefault();
        form.patch(`/admin/expenses/${expense.id}/status`, {
            preserveScroll: true,
        });
    }

    return (
        <>
            <Head title={expense.title} />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                            Operations
                        </p>
                        <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight">
                            {expense.title}
                        </h2>
                        <div className="mt-2 flex flex-wrap items-center gap-2">
                            <StatusBadge
                                label={expense.status_label}
                                badge={expense.status_badge}
                            />
                            <span className="text-sm text-muted-foreground">
                                {expense.category_name}
                                {expense.date_formatted
                                    ? ` · ${expense.date_formatted}`
                                    : ''}
                            </span>
                        </div>
                    </div>
                    <div className="flex flex-wrap gap-2">
                        <Button variant="outline" asChild>
                            <Link href="/admin/expenses">Back</Link>
                        </Button>
                        {canManage && (
                            <Button asChild>
                                <Link
                                    href={`/admin/expenses/${expense.id}/edit`}
                                >
                                    Edit
                                </Link>
                            </Button>
                        )}
                        {canManage && (
                            <Button
                                variant="destructive"
                                type="button"
                                onClick={() => destroyExpense(expense.id)}
                            >
                                Delete
                            </Button>
                        )}
                    </div>
                </div>

                <div className="grid gap-4 rounded-xl border border-border/80 bg-card p-4 md:grid-cols-3 md:p-6">
                    <Detail
                        label="Quantity"
                        value={`${expense.quantity}${expense.unit ? ` ${expense.unit}` : ''}`}
                    />
                    <Detail
                        label="Unit price"
                        value={expense.unit_price_formatted}
                    />
                    <Detail label="Total" value={expense.total_formatted} />
                    <Detail
                        label="Recorded by"
                        value={expense.created_by || '—'}
                    />
                    <div className="md:col-span-2">
                        <Detail
                            label="Notes"
                            value={expense.description || '—'}
                        />
                    </div>
                </div>

                {expense.receipt_image_url && (
                    <div className="rounded-xl border border-border/80 bg-card p-4 md:p-6">
                        <p className="text-[11px] font-bold tracking-[0.08em] text-muted-foreground uppercase">
                            Receipt
                        </p>
                        <img
                            src={expense.receipt_image_url}
                            alt="Expense receipt"
                            className="mt-3 max-h-80 rounded-lg border object-contain"
                        />
                    </div>
                )}

                {canManage && (
                    <form
                        onSubmit={submitStatus}
                        className="flex flex-wrap items-end gap-3 rounded-xl border border-border/80 bg-card p-4"
                    >
                        <div>
                            <Label htmlFor="status">Update status</Label>
                            <select
                                id="status"
                                className="border-input mt-2 flex h-9 rounded-md border bg-background px-3 text-sm"
                                value={form.data.status}
                                onChange={(e) =>
                                    form.setData('status', e.target.value)
                                }
                            >
                                {statusOptions.map((option) => (
                                    <option
                                        key={option.value}
                                        value={option.value}
                                    >
                                        {option.label}
                                    </option>
                                ))}
                            </select>
                        </div>
                        <Button type="submit" disabled={form.processing}>
                            Save status
                        </Button>
                    </form>
                )}
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

ExpensesShow.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Expenses', href: '/admin/expenses' },
        { title: 'Details', href: '#' },
    ],
};
