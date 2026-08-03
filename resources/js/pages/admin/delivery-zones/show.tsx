import { Head, Link } from '@inertiajs/react';
import { StatusBadge } from '@/components/status-badge/status-badge';
import { StatusToggle } from '@/components/status-toggle';
import { Button } from '@/components/ui/button';
import {
    destroyDeliveryZone,
    type AdminDeliveryZone,
} from '@/pages/admin/delivery-zones/zone-form';

type Props = {
    zone: AdminDeliveryZone;
    canManage: boolean;
};

export default function DeliveryZonesShow({ zone, canManage }: Props) {
    return (
        <>
            <Head title={zone.name} />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                            Operations
                        </p>
                        <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight">
                            {zone.name}
                        </h2>
                        <div className="mt-2 flex flex-wrap items-center gap-2">
                            <StatusBadge
                                label={zone.status_label}
                                badge={zone.status_badge}
                            />
                            <span className="text-sm font-medium">
                                {zone.fee_formatted}
                            </span>
                        </div>
                    </div>
                    <div className="flex flex-wrap items-center gap-2">
                        <StatusToggle
                            endpoint={`/admin/delivery-zones/${zone.id}/status`}
                            status={zone.status}
                            canToggle={canManage}
                            activeLabel="Deactivate zone"
                            inactiveLabel="Activate zone"
                        />
                        <Button variant="outline" asChild>
                            <Link href="/admin/delivery-zones">Back</Link>
                        </Button>
                        {canManage && (
                            <Button asChild>
                                <Link
                                    href={`/admin/delivery-zones/${zone.id}/edit`}
                                >
                                    Edit
                                </Link>
                            </Button>
                        )}
                        {canManage && zone.orders_count === 0 && (
                            <Button
                                variant="destructive"
                                type="button"
                                onClick={() => destroyDeliveryZone(zone.id)}
                            >
                                Delete
                            </Button>
                        )}
                    </div>
                </div>

                <div className="grid gap-4 rounded-xl border border-border/80 bg-card p-4 md:grid-cols-3 md:p-6">
                    <Detail label="Fee" value={zone.fee_formatted} />
                    <Detail
                        label="Orders using zone"
                        value={String(zone.orders_count)}
                    />
                    <Detail label="Sort order" value={String(zone.sort_order)} />
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

DeliveryZonesShow.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Delivery Zones', href: '/admin/delivery-zones' },
        { title: 'Details', href: '#' },
    ],
};
