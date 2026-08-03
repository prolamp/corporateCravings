import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import {
    destroyExpenseCategory,
    ExpenseCategoryForm,
    type AdminExpenseCategory,
    type StatusOption,
} from '@/pages/admin/expense-categories/category-form';

type Props = {
    category: AdminExpenseCategory;
    statusOptions: StatusOption[];
};

export default function ExpenseCategoriesEdit({
    category,
    statusOptions,
}: Props) {
    return (
        <>
            <Head title={`Edit ${category.name}`} />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                            Operations
                        </p>
                        <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight">
                            Edit {category.name}
                        </h2>
                    </div>
                    <div className="flex gap-2">
                        <Button variant="outline" asChild>
                            <Link
                                href={`/admin/expense-categories/${category.id}`}
                            >
                                View
                            </Link>
                        </Button>
                        {category.expenses_count === 0 && (
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
                <div className="max-w-2xl rounded-xl border border-border/80 bg-card p-4 md:p-6">
                    <ExpenseCategoryForm
                        mode="edit"
                        action={`/admin/expense-categories/${category.id}`}
                        method="put"
                        category={category}
                        statusOptions={statusOptions}
                        cancelHref={`/admin/expense-categories/${category.id}`}
                    />
                </div>
            </div>
        </>
    );
}

ExpenseCategoriesEdit.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        {
            title: 'Expense Categories',
            href: '/admin/expense-categories',
        },
        { title: 'Edit', href: '#' },
    ],
};
