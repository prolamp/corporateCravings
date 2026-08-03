import { Head } from '@inertiajs/react';
import {
    DishForm,
    type SelectOption,
    type StatusOption,
} from '@/pages/admin/dishes/dish-form';

type Props = {
    categoryOptions: SelectOption[];
    statusOptions: StatusOption[];
    unitOptions: SelectOption[];
    typeOptions: SelectOption[];
};

export default function DishesCreate({
    categoryOptions,
    statusOptions,
    unitOptions,
    typeOptions,
}: Props) {
    return (
        <>
            <Head title="Add dish" />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div>
                    <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                        Catalog
                    </p>
                    <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight">
                        Add dish
                    </h2>
                </div>
                <div className="max-w-5xl rounded-xl border border-border/80 bg-card p-4 md:p-6">
                    <DishForm
                        mode="create"
                        action="/admin/dishes"
                        categoryOptions={categoryOptions}
                        statusOptions={statusOptions}
                        unitOptions={unitOptions}
                        typeOptions={typeOptions}
                        cancelHref="/admin/dishes"
                    />
                </div>
            </div>
        </>
    );
}

DishesCreate.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Dishes', href: '/admin/dishes' },
        { title: 'Add', href: '/admin/dishes/create' },
    ],
};
