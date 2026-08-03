import { Head } from '@inertiajs/react';
import {
    CategoryForm,
    type StatusOption,
} from '@/pages/admin/categories/category-form';

type Props = {
    statusOptions: StatusOption[];
};

export default function CategoriesCreate({ statusOptions }: Props) {
    return (
        <>
            <Head title="Add category" />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div>
                    <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                        Catalog
                    </p>
                    <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight">
                        Add category
                    </h2>
                </div>
                <div className="max-w-3xl rounded-xl border border-border/80 bg-card p-4 md:p-6">
                    <CategoryForm
                        mode="create"
                        action="/admin/categories"
                        statusOptions={statusOptions}
                        cancelHref="/admin/categories"
                    />
                </div>
            </div>
        </>
    );
}

CategoriesCreate.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Categories', href: '/admin/categories' },
        { title: 'Add', href: '/admin/categories/create' },
    ],
};
