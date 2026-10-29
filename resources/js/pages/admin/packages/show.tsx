import { Head, Link } from '@inertiajs/react';
import { StatusBadge } from '@/components/status-badge/status-badge';
import { StatusToggle } from '@/components/status-toggle';
import { Button } from '@/components/ui/button';
import {
    destroyPackage,
    type AdminPackage,
} from '@/pages/admin/packages/package-form';

type Props = {
    package: AdminPackage;
    canManage: boolean;
};

export default function PackagesShow({
    package: pkg,
    canManage,
}: Props) {
    return (
        <>
            <Head title={pkg.title} />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div className="flex items-start gap-4">
                        {pkg.image_url ? (
                            <img
                                src={pkg.image_url}
                                alt=""
                                className="size-24 rounded-xl object-cover"
                            />
                        ) : (
                            <div className="flex size-24 items-center justify-center rounded-xl bg-muted font-display text-2xl font-semibold text-muted-foreground">
                                {pkg.title.slice(0, 1).toUpperCase()}
                            </div>
                        )}
                        <div>
                            <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                                Packages
                            </p>
                            <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight">
                                {pkg.title}
                            </h2>
                            <div className="mt-2 flex flex-wrap items-center gap-2">
                                <StatusBadge
                                    label={pkg.status_label}
                                    badge={pkg.status_badge}
                                />
                                <StatusBadge
                                    label={
                                        pkg.show_on_website
                                            ? 'On website'
                                            : 'Hidden from website'
                                    }
                                    badge={
                                        pkg.show_on_website
                                            ? 'success'
                                            : 'default'
                                    }
                                />
                                <span className="text-sm font-medium">
                                    {pkg.total_amount_per_person_formatted} /pp
                                </span>
                            </div>
                            {pkg.short_description && (
                                <p className="mt-3 max-w-2xl text-sm text-muted-foreground">
                                    {pkg.short_description}
                                </p>
                            )}
                        </div>
                    </div>
                    <div className="flex flex-wrap items-center gap-2">
                        <StatusToggle
                            endpoint={`/admin/packages/${pkg.id}/status`}
                            status={pkg.status}
                            canToggle={canManage}
                            activeLabel="Deactivate package"
                            inactiveLabel="Activate package"
                        />
                        <Button variant="outline" asChild>
                            <Link href="/admin/packages">Back</Link>
                        </Button>
                        {canManage && (
                            <Button asChild>
                                <Link href={`/admin/packages/${pkg.id}/edit`}>
                                    Edit
                                </Link>
                            </Button>
                        )}
                        {canManage && (
                            <Button
                                variant="destructive"
                                type="button"
                                onClick={() => destroyPackage(pkg.id)}
                            >
                                Delete
                            </Button>
                        )}
                    </div>
                </div>

                <div className="grid gap-4 rounded-xl border border-border/80 bg-card p-4 md:grid-cols-3 md:p-6">
                    <Detail
                        label="Avg base / person"
                        value={pkg.base_amount_per_person_formatted}
                    />
                    <Detail
                        label="Discount"
                        value={`${pkg.discount_amount > 0 ? `PKR ${pkg.discount_amount.toFixed(2)}` : '—'}${
                            pkg.discount_percent > 0
                                ? ` + ${pkg.discount_percent}%`
                                : ''
                        }`}
                    />
                    <Detail
                        label="Public total / person"
                        value={pkg.total_amount_per_person_formatted}
                    />
                </div>

                <div className="overflow-hidden rounded-xl border border-border/80 bg-card">
                    <div className="border-b border-border/60 px-4 py-3">
                        <h3 className="font-display text-lg font-semibold tracking-tight">
                            Included dishes
                        </h3>
                    </div>
                    <div className="overflow-x-auto">
                        <table className="w-full min-w-[520px] text-left text-sm">
                            <thead className="bg-muted/40 text-[11px] font-bold tracking-wider text-muted-foreground uppercase">
                                <tr>
                                    <th className="px-4 py-3">#</th>
                                    <th className="px-4 py-3">Dish</th>
                                    <th className="px-4 py-3">
                                        Dish price
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-border/60">
                                {pkg.items.map((item, index) => (
                                    <tr key={item.id ?? item.dish_id}>
                                        <td className="px-4 py-3">
                                            {index + 1}
                                        </td>
                                        <td className="px-4 py-3 font-medium">
                                            {item.dish_title ??
                                                `Dish #${item.dish_id}`}
                                        </td>
                                        <td className="px-4 py-3">
                                            {item.unit_price_formatted}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
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

PackagesShow.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Packages', href: '/admin/packages' },
        { title: 'Details', href: '#' },
    ],
};
