import { Head } from '@inertiajs/react';
import {
    DeliveryZoneForm,
    type StatusOption,
} from '@/pages/admin/delivery-zones/zone-form';

type Props = {
    statusOptions: StatusOption[];
};

export default function DeliveryZonesCreate({ statusOptions }: Props) {
    return (
        <>
            <Head title="Add delivery zone" />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div>
                    <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                        Operations
                    </p>
                    <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight">
                        Add delivery zone
                    </h2>
                </div>
                <div className="max-w-2xl rounded-xl border border-border/80 bg-card p-4 md:p-6">
                    <DeliveryZoneForm
                        mode="create"
                        action="/admin/delivery-zones"
                        statusOptions={statusOptions}
                        cancelHref="/admin/delivery-zones"
                    />
                </div>
            </div>
        </>
    );
}

DeliveryZonesCreate.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Delivery Zones', href: '/admin/delivery-zones' },
        { title: 'Add', href: '/admin/delivery-zones/create' },
    ],
};
