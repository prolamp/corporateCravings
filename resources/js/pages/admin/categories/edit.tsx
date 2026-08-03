import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import {
    CategoryForm,
    destroyCategory,
    type AdminCategory,
    type StatusOption,
} from '@/pages/admin/categories/category-form';

type Props = {
    category: AdminCategory;
    statusOptions: StatusOption[];
};

export default function CategoriesEdit({ category, statusOptions }: Props) {
    return (
        <>
            <Head title={`Edit ${category.name}`} />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                            Catalog
                        </p>
                        <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight">
                            Edit {category.name}
                        </h2>
                    </div>
                    <div className="flex gap-2">
                        <Button variant="outline" asChild>
                            <Link href={`/admin/categories/${category.id}`}>
                                View
                            </Link>
                        </Button>
                        {category.dishes_count === 0 && (
                            <Button
                                variant="destructive"
                                type="button"
                                onClick={() => destroyCategory(category.id)}
                            >
                                Delete
                            </Button>
                        )}
                    </div>
                </div>
                <div className="max-w-3xl rounded-xl border border-border/80 bg-card p-4 md:p-6">
                    <CategoryForm
                        mode="edit"
                        action={`/admin/categories/${category.id}`}
                        method="put"
                        category={category}
                        statusOptions={statusOptions}
                        cancelHref={`/admin/categories/${category.id}`}
                    />
                </div>
            </div>
        </>
    );
}

CategoriesEdit.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Categories', href: '/admin/categories' },
        { title: 'Edit', href: '#' },
    ],
};
