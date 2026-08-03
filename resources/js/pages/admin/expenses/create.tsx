import { Head } from '@inertiajs/react';
import {
    ExpenseForm,
    type SelectOption,
    type StatusOption,
} from '@/pages/admin/expenses/expense-form';

type Props = {
    statusOptions: StatusOption[];
    categoryOptions: SelectOption[];
};

export default function ExpensesCreate({
    statusOptions,
    categoryOptions,
}: Props) {
    return (
        <>
            <Head title="Add expense" />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div>
                    <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                        Operations
                    </p>
                    <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight">
                        Add expense
                    </h2>
                </div>
                <div className="max-w-3xl rounded-xl border border-border/80 bg-card p-4 md:p-6">
                    <ExpenseForm
                        mode="create"
                        action="/admin/expenses"
                        statusOptions={statusOptions}
                        categoryOptions={categoryOptions}
                        cancelHref="/admin/expenses"
                    />
                </div>
            </div>
        </>
    );
}

ExpensesCreate.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Expenses', href: '/admin/expenses' },
        { title: 'Add', href: '/admin/expenses/create' },
    ],
};
