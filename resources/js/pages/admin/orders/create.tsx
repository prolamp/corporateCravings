import { Head } from '@inertiajs/react';
import {
    OrderForm,
    type AddonOption,
    type VariationOption,
    type ZoneOption,
} from '@/pages/admin/orders/order-form';
import type { StatusOption } from '@/pages/admin/orders/index';

type Props = {
    statusOptions: StatusOption[];
    typeOptions: StatusOption[];
    zoneOptions: ZoneOption[];
    variationOptions: VariationOption[];
    addonOptions: AddonOption[];
};

export default function OrdersCreate({
    statusOptions,
    typeOptions,
    zoneOptions,
    variationOptions,
    addonOptions,
}: Props) {
    return (
        <>
            <Head title="New order" />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div>
                    <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                        Orders
                    </p>
                    <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight">
                        Create order
                    </h2>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Manual event or individual order with variations,
                        optional addons, and list → discount → quoted pricing.
                    </p>
                </div>

                <OrderForm
                    mode="create"
                    action="/admin/orders"
                    statusOptions={statusOptions}
                    typeOptions={typeOptions}
                    zoneOptions={zoneOptions}
                    variationOptions={variationOptions}
                    addonOptions={addonOptions}
                    cancelHref="/admin/orders"
                />
            </div>
        </>
    );
}

OrdersCreate.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Orders', href: '/admin/orders' },
        { title: 'Create', href: '#' },
    ],
};
