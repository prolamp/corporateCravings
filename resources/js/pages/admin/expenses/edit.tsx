import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import {
    destroyExpense,
    ExpenseForm,
    type AdminExpense,
    type SelectOption,
    type StatusOption,
} from '@/pages/admin/expenses/expense-form';

type Props = {
    expense: AdminExpense;
    statusOptions: StatusOption[];
    categoryOptions: SelectOption[];
};

export default function ExpensesEdit({
    expense,
    statusOptions,
    categoryOptions,
}: Props) {
    return (
        <>
            <Head title={`Edit ${expense.title}`} />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                            Operations
                        </p>
                        <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight">
                            Edit {expense.title}
                        </h2>
                    </div>
                    <div className="flex gap-2">
                        <Button variant="outline" asChild>
                            <Link href={`/admin/expenses/${expense.id}`}>
                                View
                            </Link>
                        </Button>
                        <Button
                            variant="destructive"
                            type="button"
                            onClick={() => destroyExpense(expense.id)}
                        >
                            Delete
                        </Button>
                    </div>
                </div>
                <div className="max-w-3xl rounded-xl border border-border/80 bg-card p-4 md:p-6">
                    <ExpenseForm
                        mode="edit"
                        action={`/admin/expenses/${expense.id}`}
                        method="put"
                        expense={expense}
                        statusOptions={statusOptions}
                        categoryOptions={categoryOptions}
                        cancelHref={`/admin/expenses/${expense.id}`}
                    />
                </div>
            </div>
        </>
    );
}

ExpensesEdit.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Expenses', href: '/admin/expenses' },
        { title: 'Edit', href: '#' },
    ],
};
