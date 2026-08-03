import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import {
    destroyDish,
    DishForm,
    type AdminDish,
    type SelectOption,
    type StatusOption,
} from '@/pages/admin/dishes/dish-form';

type Props = {
    dish: AdminDish;
    categoryOptions: SelectOption[];
    statusOptions: StatusOption[];
    unitOptions: SelectOption[];
    typeOptions: SelectOption[];
};

export default function DishesEdit({
    dish,
    categoryOptions,
    statusOptions,
    unitOptions,
    typeOptions,
}: Props) {
    return (
        <>
            <Head title={`Edit ${dish.title}`} />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                            Catalog
                        </p>
                        <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight">
                            Edit {dish.title}
                        </h2>
                    </div>
                    <div className="flex gap-2">
                        <Button variant="outline" asChild>
                            <Link href={`/admin/dishes/${dish.id}`}>View</Link>
                        </Button>
                        <Button
                            variant="destructive"
                            type="button"
                            onClick={() => destroyDish(dish.id)}
                        >
                            Delete
                        </Button>
                    </div>
                </div>
                <div className="max-w-5xl rounded-xl border border-border/80 bg-card p-4 md:p-6">
                    <DishForm
                        mode="edit"
                        action={`/admin/dishes/${dish.id}`}
                        method="put"
                        dish={dish}
                        categoryOptions={categoryOptions}
                        statusOptions={statusOptions}
                        unitOptions={unitOptions}
                        typeOptions={typeOptions}
                        cancelHref={`/admin/dishes/${dish.id}`}
                    />
                </div>
            </div>
        </>
    );
}

DishesEdit.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Dishes', href: '/admin/dishes' },
        { title: 'Edit', href: '#' },
    ],
};
