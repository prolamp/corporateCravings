import { Head, Link } from '@inertiajs/react';
import { StatusBadge } from '@/components/status-badge/status-badge';
import { StatusToggle } from '@/components/status-toggle';
import { Button } from '@/components/ui/button';
import {
    destroyDish,
    type AdminDish,
} from '@/pages/admin/dishes/dish-form';

type Props = {
    dish: AdminDish;
    canManage: boolean;
};

export default function DishesShow({ dish, canManage }: Props) {
    return (
        <>
            <Head title={dish.title} />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div className="flex items-start gap-4">
                        {dish.image_url ? (
                            <img
                                src={dish.image_url}
                                alt=""
                                className="size-24 rounded-xl object-cover"
                            />
                        ) : (
                            <div className="flex size-24 items-center justify-center rounded-xl bg-muted font-display text-2xl font-semibold text-muted-foreground">
                                {dish.title.slice(0, 1).toUpperCase()}
                            </div>
                        )}
                        <div>
                            <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                                Catalog
                            </p>
                            <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight">
                                {dish.title}
                            </h2>
                            <div className="mt-2 flex flex-wrap items-center gap-2">
                                <StatusBadge
                                    label={dish.status_label}
                                    badge={dish.status_badge}
                                />
                                {dish.category_name && (
                                    <span className="rounded-md border px-2 py-0.5 text-xs font-medium">
                                        {dish.category_name}
                                    </span>
                                )}
                                {dish.starting_price_formatted && (
                                    <span className="text-sm font-medium">
                                        From {dish.starting_price_formatted}
                                    </span>
                                )}
                            </div>
                            {dish.short_description && (
                                <p className="mt-3 max-w-2xl text-sm text-muted-foreground">
                                    {dish.short_description}
                                </p>
                            )}
                        </div>
                    </div>
                    <div className="flex flex-wrap items-center gap-2">
                        <StatusToggle
                            endpoint={`/admin/dishes/${dish.id}/status`}
                            status={dish.status}
                            canToggle={canManage}
                            activeLabel="Deactivate dish"
                            inactiveLabel="Activate dish"
                        />
                        <Button variant="outline" asChild>
                            <Link href="/admin/dishes">Back</Link>
                        </Button>
                        {canManage && (
                            <Button asChild>
                                <Link href={`/admin/dishes/${dish.id}/edit`}>
                                    Edit
                                </Link>
                            </Button>
                        )}
                        {canManage && (
                            <Button
                                variant="destructive"
                                type="button"
                                onClick={() => destroyDish(dish.id)}
                            >
                                Delete
                            </Button>
                        )}
                    </div>
                </div>

                <div className="overflow-hidden rounded-xl border border-border/80 bg-card">
                    <div className="border-b border-border/60 px-4 py-3">
                        <h3 className="font-display text-lg font-semibold tracking-tight">
                            Variations
                        </h3>
                        <p className="text-sm text-muted-foreground">
                            Single = cartable · Bulk = WhatsApp quote
                        </p>
                    </div>
                    {dish.variations.length === 0 ? (
                        <div className="px-4 py-8 text-sm text-muted-foreground">
                            No variations yet.
                        </div>
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="w-full min-w-[640px] text-left text-sm">
                                <thead className="bg-muted/40 text-[11px] font-bold tracking-wider text-muted-foreground uppercase">
                                    <tr>
                                        <th className="px-4 py-3">Label</th>
                                        <th className="px-4 py-3">Qty</th>
                                        <th className="px-4 py-3">Unit</th>
                                        <th className="px-4 py-3">Price</th>
                                        <th className="px-4 py-3">Type</th>
                                        <th className="px-4 py-3">Status</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-border/60">
                                    {dish.variations.map((variation) => (
                                        <tr key={variation.id}>
                                            <td className="px-4 py-3 font-medium">
                                                {variation.label}
                                            </td>
                                            <td className="px-4 py-3">
                                                {variation.quantity}
                                            </td>
                                            <td className="px-4 py-3 uppercase">
                                                {variation.unit}
                                            </td>
                                            <td className="px-4 py-3">
                                                {variation.price_formatted}
                                            </td>
                                            <td className="px-4 py-3">
                                                <span className="rounded-md border px-2 py-0.5 text-xs font-medium">
                                                    {variation.type_label}
                                                    {variation.cartable
                                                        ? ' · Cart'
                                                        : ' · WA'}
                                                </span>
                                            </td>
                                            <td className="px-4 py-3">
                                                <StatusBadge
                                                    label={
                                                        variation.status_label ??
                                                        variation.status
                                                    }
                                                    badge={
                                                        variation.status_badge ??
                                                        'default'
                                                    }
                                                />
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}
                </div>
            </div>
        </>
    );
}

DishesShow.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Dishes', href: '/admin/dishes' },
        { title: 'Details', href: '#' },
    ],
};
