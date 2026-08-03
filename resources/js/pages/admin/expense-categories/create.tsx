import { Head } from '@inertiajs/react';
import {
    ExpenseCategoryForm,
    type StatusOption,
} from '@/pages/admin/expense-categories/category-form';

type Props = {
    statusOptions: StatusOption[];
};

export default function ExpenseCategoriesCreate({ statusOptions }: Props) {
    return (
        <>
            <Head title="Add expense category" />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div>
                    <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                        Operations
                    </p>
                    <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight">
                        Add expense category
                    </h2>
                </div>
                <div className="max-w-2xl rounded-xl border border-border/80 bg-card p-4 md:p-6">
                    <ExpenseCategoryForm
                        mode="create"
                        action="/admin/expense-categories"
                        statusOptions={statusOptions}
                        cancelHref="/admin/expense-categories"
                    />
                </div>
            </div>
        </>
    );
}

ExpenseCategoriesCreate.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        {
            title: 'Expense Categories',
            href: '/admin/expense-categories',
        },
        { title: 'Add', href: '/admin/expense-categories/create' },
    ],
};
