import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import {
    DeliveryZoneForm,
    destroyDeliveryZone,
    type AdminDeliveryZone,
    type StatusOption,
} from '@/pages/admin/delivery-zones/zone-form';

type Props = {
    zone: AdminDeliveryZone;
    statusOptions: StatusOption[];
};

export default function DeliveryZonesEdit({ zone, statusOptions }: Props) {
    return (
        <>
            <Head title={`Edit ${zone.name}`} />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                            Operations
                        </p>
                        <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight">
                            Edit {zone.name}
                        </h2>
                    </div>
                    <div className="flex gap-2">
                        <Button variant="outline" asChild>
                            <Link href={`/admin/delivery-zones/${zone.id}`}>
                                View
                            </Link>
                        </Button>
                        {zone.orders_count === 0 && (
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
                <div className="max-w-2xl rounded-xl border border-border/80 bg-card p-4 md:p-6">
                    <DeliveryZoneForm
                        mode="edit"
                        action={`/admin/delivery-zones/${zone.id}`}
                        method="put"
                        zone={zone}
                        statusOptions={statusOptions}
                        cancelHref={`/admin/delivery-zones/${zone.id}`}
                    />
                </div>
            </div>
        </>
    );
}

DeliveryZonesEdit.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Delivery Zones', href: '/admin/delivery-zones' },
        { title: 'Edit', href: '#' },
    ],
};
