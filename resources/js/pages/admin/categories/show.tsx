import { Head, Link } from '@inertiajs/react';
import { StatusBadge } from '@/components/status-badge/status-badge';
import { StatusToggle } from '@/components/status-toggle';
import { Button } from '@/components/ui/button';
import {
    destroyCategory,
    type AdminCategory,
} from '@/pages/admin/categories/category-form';

type Props = {
    category: AdminCategory;
    canManage: boolean;
};

export default function CategoriesShow({ category, canManage }: Props) {
    return (
        <>
            <Head title={category.name} />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div className="flex items-start gap-4">
                        {category.image_url ? (
                            <img
                                src={category.image_url}
                                alt=""
                                className="size-20 rounded-xl object-cover"
                            />
                        ) : (
                            <div className="flex size-20 items-center justify-center rounded-xl bg-muted font-display text-2xl font-semibold text-muted-foreground">
                                {category.name.slice(0, 1).toUpperCase()}
                            </div>
                        )}
                        <div>
                            <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                                Catalog
                            </p>
                            <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight">
                                {category.name}
                            </h2>
                            <div className="mt-2 flex flex-wrap items-center gap-2">
                                <StatusBadge
                                    label={category.status_label}
                                    badge={category.status_badge}
                                />
                                <span className="rounded-md border px-2 py-0.5 text-xs font-medium">
                                    {category.dishes_count} dishes
                                </span>
                            </div>
                            <p className="mt-2 text-sm text-muted-foreground">
                                Slug:{' '}
                                <code className="text-foreground">
                                    {category.slug}
                                </code>
                            </p>
                        </div>
                    </div>
                    <div className="flex flex-wrap items-center gap-2">
                        <StatusToggle
                            endpoint={`/admin/categories/${category.id}/status`}
                            status={category.status}
                            canToggle={canManage}
                            activeLabel="Deactivate category"
                            inactiveLabel="Activate category"
                        />
                        <Button variant="outline" asChild>
                            <Link href="/admin/categories">Back</Link>
                        </Button>
                        {canManage && (
                            <Button asChild>
                                <Link
                                    href={`/admin/categories/${category.id}/edit`}
                                >
                                    Edit
                                </Link>
                            </Button>
                        )}
                        {canManage && category.dishes_count === 0 && (
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

                <div className="grid gap-4 rounded-xl border border-border/80 bg-card p-4 md:grid-cols-3 md:p-6">
                    <Detail label="Sort order" value={String(category.sort_order)} />
                    <Detail
                        label="Dishes"
                        value={String(category.dishes_count)}
                    />
                    <Detail label="Status" value={category.status_label} />
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
            <p className="mt-1 text-sm font-medium">{value}</p>
        </div>
    );
}

CategoriesShow.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Categories', href: '/admin/categories' },
        { title: 'Details', href: '#' },
    ],
};
