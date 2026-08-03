import { Head, Link, router } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { Pagination } from '@/components/pagination';
import { StatusBadge } from '@/components/status-badge/status-badge';
import { StatusToggle } from '@/components/status-toggle';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type {
    AdminDeliveryZone,
    StatusOption,
} from '@/pages/admin/delivery-zones/zone-form';

type Paginator = {
    data: AdminDeliveryZone[];
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number | null;
        to: number | null;
    };
};

type Props = {
    zones: Paginator;
    filters: {
        search: string | null;
        status: string | null;
    };
    statusOptions: StatusOption[];
    canManage: boolean;
};

export default function DeliveryZonesIndex({
    zones,
    filters,
    statusOptions,
    canManage,
}: Props) {
    function applyFilters(next: Partial<Props['filters']>) {
        router.get(
            '/admin/delivery-zones',
            {
                search: next.search ?? filters.search ?? '',
                status: next.status ?? filters.status ?? '',
            },
            { preserveState: true, replace: true },
        );
    }

    function pageUrl(page: number): string {
        const params = new URLSearchParams();
        if (filters.search) {
            params.set('search', filters.search);
        }
        if (filters.status) {
            params.set('status', filters.status);
        }
        if (page > 1) {
            params.set('page', String(page));
        }
        const query = params.toString();

        return query
            ? `/admin/delivery-zones?${query}`
            : '/admin/delivery-zones';
    }

    return (
        <>
            <Head title="Delivery Zones" />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                            Operations
                        </p>
                        <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight md:text-3xl">
                            Delivery zones
                        </h2>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Checkout applies the selected zone’s fee to COD
                            totals.
                        </p>
                    </div>
                    {canManage && (
                        <Button asChild>
                            <Link href="/admin/delivery-zones/create">
                                <Plus className="size-4" />
                                Add zone
                            </Link>
                        </Button>
                    )}
                </div>

                <div className="grid gap-3 rounded-xl border border-border/80 bg-card p-4 md:grid-cols-3">
                    <Input
                        placeholder="Search zone name…"
                        defaultValue={filters.search ?? ''}
                        onKeyDown={(e) => {
                            if (e.key === 'Enter') {
                                applyFilters({
                                    search: (
                                        e.target as HTMLInputElement
                                    ).value,
                                });
                            }
                        }}
                    />
                    <select
                        className="border-input h-9 rounded-md border bg-background px-3 text-sm"
                        value={filters.status ?? ''}
                        onChange={(e) =>
                            applyFilters({ status: e.target.value || null })
                        }
                    >
                        <option value="">All statuses</option>
                        {statusOptions.map((option) => (
                            <option key={option.value} value={option.value}>
                                {option.label}
                            </option>
                        ))}
                    </select>
                    <Button
                        type="button"
                        variant="outline"
                        onClick={() =>
                            router.get(
                                '/admin/delivery-zones',
                                {},
                                { replace: true },
                            )
                        }
                    >
                        Clear filters
                    </Button>
                </div>

                <div className="overflow-hidden rounded-xl border border-border/80 bg-card">
                    {zones.data.length === 0 ? (
                        <div className="px-4 py-12 text-center text-sm text-muted-foreground">
                            No delivery zones match these filters.
                        </div>
                    ) : (
                        <>
                            <div className="overflow-x-auto">
                                <table className="w-full min-w-[640px] text-left text-sm">
                                    <thead className="bg-muted/40 text-[11px] font-bold tracking-wider text-muted-foreground uppercase">
                                        <tr>
                                            <th className="px-4 py-3">Zone</th>
                                            <th className="px-4 py-3">Fee</th>
                                            <th className="px-4 py-3">
                                                Orders
                                            </th>
                                            <th className="px-4 py-3">
                                                Status
                                            </th>
                                            <th className="px-4 py-3 text-right">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-border/60">
                                        {zones.data.map((zone) => (
                                            <tr key={zone.id}>
                                                <td className="px-4 py-3 font-medium">
                                                    {zone.name}
                                                </td>
                                                <td className="px-4 py-3">
                                                    {zone.fee_formatted}
                                                </td>
                                                <td className="px-4 py-3">
                                                    {zone.orders_count}
                                                </td>
                                                <td className="px-4 py-3">
                                                    <StatusBadge
                                                        label={
                                                            zone.status_label
                                                        }
                                                        badge={
                                                            zone.status_badge
                                                        }
                                                    />
                                                </td>
                                                <td className="px-4 py-3 text-right">
                                                    <div className="flex flex-wrap items-center justify-end gap-2">
                                                        <StatusToggle
                                                            endpoint={`/admin/delivery-zones/${zone.id}/status`}
                                                            status={zone.status}
                                                            canToggle={
                                                                canManage
                                                            }
                                                            activeLabel="Deactivate zone"
                                                            inactiveLabel="Activate zone"
                                                        />
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            asChild
                                                        >
                                                            <Link
                                                                href={`/admin/delivery-zones/${zone.id}`}
                                                            >
                                                                View
                                                            </Link>
                                                        </Button>
                                                        {canManage && (
                                                            <Button
                                                                variant="outline"
                                                                size="sm"
                                                                asChild
                                                            >
                                                                <Link
                                                                    href={`/admin/delivery-zones/${zone.id}/edit`}
                                                                >
                                                                    Edit
                                                                </Link>
                                                            </Button>
                                                        )}
                                                    </div>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                            <Pagination
                                meta={zones.meta}
                                pageUrl={pageUrl}
                            />
                        </>
                    )}
                </div>
            </div>
        </>
    );
}

DeliveryZonesIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Delivery Zones', href: '/admin/delivery-zones' },
    ],
};
