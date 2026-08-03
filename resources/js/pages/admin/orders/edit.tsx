import { Head } from '@inertiajs/react';
import {
    OrderForm,
    type AddonOption,
    type VariationOption,
    type ZoneOption,
} from '@/pages/admin/orders/order-form';
import type { StatusOption } from '@/pages/admin/orders/index';

type OrderEdit = {
    id: number;
    order_number: string;
    type: string;
    status: string;
    customer_name: string | null;
    customer_phone: string | null;
    customer_email: string | null;
    delivery_address: string | null;
    delivery_zone_id: number | null;
    delivery_fee: number;
    discount_amount: number;
    discount_percent: number;
    amount_paid: number | null;
    notes: string | null;
    delivery_person_name: string | null;
    items: Array<{
        dish_variation_id?: number | null;
        quantity: number;
        unit_price: number;
        addons: Array<{
            addon_id: number | null;
            quantity: number;
            title: string;
        }>;
    }>;
};

type Props = {
    order: OrderEdit;
    statusOptions: StatusOption[];
    typeOptions: StatusOption[];
    zoneOptions: ZoneOption[];
    variationOptions: VariationOption[];
    addonOptions: AddonOption[];
};

export default function OrdersEdit({
    order,
    statusOptions,
    typeOptions,
    zoneOptions,
    variationOptions,
    addonOptions,
}: Props) {
    return (
        <>
            <Head title={`Edit ${order.order_number}`} />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div>
                    <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                        Orders
                    </p>
                    <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight">
                        Edit {order.order_number}
                    </h2>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Update customer details, line items, and pricing.
                        Subscription schedule orders stay read-only.
                    </p>
                </div>

                <OrderForm
                    mode="edit"
                    method="put"
                    action={`/admin/orders/${order.id}`}
                    order={order}
                    deliveryZoneId={order.delivery_zone_id}
                    statusOptions={statusOptions}
                    typeOptions={typeOptions}
                    zoneOptions={zoneOptions}
                    variationOptions={variationOptions}
                    addonOptions={addonOptions}
                    cancelHref={`/admin/orders/${order.id}`}
                />
            </div>
        </>
    );
}

OrdersEdit.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Orders', href: '/admin/orders' },
        { title: 'Edit', href: '#' },
    ],
};
