import { Head, Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { StatusBadge } from '@/components/status-badge/status-badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { StatusOption } from '@/pages/admin/orders/index';

type OrderItemRow = {
    id: number;
    title: string;
    variation_label: string | null;
    quantity: number;
    unit_price_formatted: string;
    line_total_formatted: string;
    addons: Array<{
        id: number;
        title: string;
        quantity: number;
        unit_price_formatted: string;
        line_total_formatted: string;
    }>;
};

type TimelineRow = {
    id: number;
    from_status_label: string | null;
    to_status: string;
    to_status_label: string;
    to_status_badge: string;
    changed_by: string | null;
    note: string | null;
    created_at_formatted: string | null;
};

type OrderDetail = {
    id: number;
    order_number: string;
    type_label: string;
    channel_label: string;
    status: string;
    status_label: string;
    status_badge: string;
    payment_method: string;
    customer_name: string | null;
    customer_phone: string | null;
    customer_email: string | null;
    delivery_address: string | null;
    delivery_zone: string | null;
    delivery_fee_formatted: string;
    subtotal_formatted: string;
    list_amount_formatted: string;
    discount_amount_formatted: string;
    quoted_amount_formatted: string;
    amount_paid_formatted: string | null;
    subscriber_company: string | null;
    subscribe_contract_id: number | null;
    schedule_date: string | null;
    notes: string | null;
    delivery_person_name: string | null;
    whatsapp_url: string | null;
    placed_at_formatted: string | null;
    can_edit: boolean;
    items: OrderItemRow[];
    timeline: TimelineRow[];
};

type Props = {
    order: OrderDetail;
    statusOptions: StatusOption[];
    canUpdateStatus: boolean;
    canManage: boolean;
};

export default function OrdersShow({
    order,
    statusOptions,
    canUpdateStatus,
    canManage,
}: Props) {
    const form = useForm({
        status: order.status,
        note: '',
        delivery_person_name: order.delivery_person_name ?? '',
    });

    function submit(event: FormEvent) {
        event.preventDefault();
        form.transform((data) => ({
            ...data,
            note: data.note || null,
            delivery_person_name: data.delivery_person_name || null,
        }));
        form.patch(`/admin/orders/${order.id}/status`, {
            preserveScroll: true,
            onSuccess: () => form.setData('note', ''),
        });
    }

    return (
        <>
            <Head title={order.order_number} />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                            Orders
                        </p>
                        <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight">
                            {order.order_number}
                        </h2>
                        <div className="mt-2 flex flex-wrap items-center gap-2">
                            <StatusBadge
                                label={order.status_label}
                                badge={order.status_badge}
                            />
                            <span className="text-sm text-muted-foreground">
                                {order.type_label} · {order.channel_label}
                                {order.placed_at_formatted
                                    ? ` · ${order.placed_at_formatted}`
                                    : ''}
                            </span>
                        </div>
                    </div>
                    <div className="flex flex-wrap gap-2">
                        <Button variant="outline" asChild>
                            <Link href="/admin/orders">Back</Link>
                        </Button>
                        {canManage && order.can_edit && (
                            <Button asChild>
                                <Link href={`/admin/orders/${order.id}/edit`}>
                                    Edit
                                </Link>
                            </Button>
                        )}
                        {order.whatsapp_url && (
                            <Button variant="outline" asChild>
                                <a
                                    href={order.whatsapp_url}
                                    target="_blank"
                                    rel="noreferrer"
                                >
                                    WhatsApp
                                </a>
                            </Button>
                        )}
                        {order.subscribe_contract_id && (
                            <Button variant="outline" asChild>
                                <Link
                                    href={`/admin/contracts/${order.subscribe_contract_id}`}
                                >
                                    Contract
                                </Link>
                            </Button>
                        )}
                    </div>
                </div>

                <div className="grid gap-4 rounded-xl border border-border/80 bg-card p-4 md:grid-cols-3 md:p-6">
                    <Detail
                        label="Customer"
                        value={
                            order.subscriber_company ||
                            order.customer_name ||
                            '—'
                        }
                    />
                    <Detail
                        label="Phone"
                        value={order.customer_phone || '—'}
                    />
                    <Detail
                        label="Email"
                        value={order.customer_email || '—'}
                    />
                    <Detail
                        label="Address"
                        value={order.delivery_address || '—'}
                    />
                    <Detail
                        label="Zone"
                        value={order.delivery_zone || '—'}
                    />
                    <Detail
                        label="Payment"
                        value={order.payment_method?.toUpperCase() || '—'}
                    />
                    {order.schedule_date && (
                        <Detail
                            label="Schedule date"
                            value={order.schedule_date}
                        />
                    )}
                    {order.delivery_person_name && (
                        <Detail
                            label="Delivery person"
                            value={order.delivery_person_name}
                        />
                    )}
                </div>

                <div className="rounded-xl border border-border/80 bg-card p-4 md:p-6">
                    <h3 className="font-display text-lg font-semibold">
                        Line items
                    </h3>
                    <div className="mt-4 overflow-x-auto">
                        <table className="w-full text-left text-sm">
                            <thead className="border-b text-[11px] font-bold tracking-[0.08em] text-muted-foreground uppercase">
                                <tr>
                                    <th className="pb-2 pr-4">Item</th>
                                    <th className="pb-2 pr-4">Qty</th>
                                    <th className="pb-2 pr-4">Unit</th>
                                    <th className="pb-2 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                {order.items.length === 0 ? (
                                    <tr>
                                        <td
                                            colSpan={4}
                                            className="py-6 text-muted-foreground"
                                        >
                                            No line items.
                                        </td>
                                    </tr>
                                ) : (
                                    order.items.map((item) => (
                                        <tr
                                            key={item.id}
                                            className="border-b last:border-0"
                                        >
                                            <td className="py-2.5 pr-4">
                                                <p className="font-medium">
                                                    {item.title}
                                                </p>
                                                {item.variation_label && (
                                                    <p className="text-xs text-muted-foreground">
                                                        {item.variation_label}
                                                    </p>
                                                )}
                                                {item.addons.map((addon) => (
                                                    <p
                                                        key={addon.id}
                                                        className="text-xs text-muted-foreground"
                                                    >
                                                        + {addon.title} ×
                                                        {addon.quantity} (
                                                        {
                                                            addon.line_total_formatted
                                                        }
                                                        )
                                                    </p>
                                                ))}
                                            </td>
                                            <td className="py-2.5 pr-4">
                                                {item.quantity}
                                            </td>
                                            <td className="py-2.5 pr-4">
                                                {item.unit_price_formatted}
                                            </td>
                                            <td className="py-2.5 text-right font-medium">
                                                {item.line_total_formatted}
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>

                    <div className="mt-4 grid gap-3 border-t border-border/60 pt-4 sm:grid-cols-2 lg:grid-cols-4">
                        <Detail
                            label="Subtotal"
                            value={order.subtotal_formatted}
                        />
                        <Detail
                            label="Delivery fee"
                            value={order.delivery_fee_formatted}
                        />
                        <Detail
                            label="Discount"
                            value={order.discount_amount_formatted}
                        />
                        <Detail
                            label="Quoted total"
                            value={order.quoted_amount_formatted}
                            emphasize
                        />
                        <Detail
                            label="Amount paid"
                            value={order.amount_paid_formatted || 'Not set'}
                            hint="Staff only — set in Phase 16 bulk flow"
                        />
                    </div>

                    {order.notes && (
                        <div className="mt-4">
                            <Detail label="Notes" value={order.notes} />
                        </div>
                    )}
                </div>

                <div className="grid gap-6 lg:grid-cols-2">
                    <div className="rounded-xl border border-border/80 bg-card p-4 md:p-6">
                        <h3 className="font-display text-lg font-semibold">
                            Status timeline
                        </h3>
                        {order.timeline.length === 0 ? (
                            <p className="mt-4 text-sm text-muted-foreground">
                                No status changes recorded yet.
                            </p>
                        ) : (
                            <ol className="mt-4 space-y-3">
                                {order.timeline.map((row) => (
                                    <li
                                        key={row.id}
                                        className="rounded-lg border border-border/60 p-3"
                                    >
                                        <div className="flex flex-wrap items-center justify-between gap-2">
                                            <StatusBadge
                                                label={row.to_status_label}
                                                badge={row.to_status_badge}
                                            />
                                            <span className="text-xs text-muted-foreground">
                                                {row.created_at_formatted}
                                            </span>
                                        </div>
                                        <p className="mt-1 text-sm text-muted-foreground">
                                            {row.from_status_label
                                                ? `${row.from_status_label} → ${row.to_status_label}`
                                                : row.to_status_label}
                                            {row.changed_by
                                                ? ` · ${row.changed_by}`
                                                : ''}
                                        </p>
                                        {row.note && (
                                            <p className="mt-1 text-sm">
                                                {row.note}
                                            </p>
                                        )}
                                    </li>
                                ))}
                            </ol>
                        )}
                    </div>

                    {canUpdateStatus && (
                        <form
                            onSubmit={submit}
                            className="rounded-xl border border-border/80 bg-card p-4 md:p-6"
                        >
                            <h3 className="font-display text-lg font-semibold">
                                Update status
                            </h3>
                            <div className="mt-4 space-y-4">
                                <div>
                                    <Label htmlFor="status">Status</Label>
                                    <select
                                        id="status"
                                        className="border-input mt-2 flex h-9 w-full rounded-md border bg-background px-3 text-sm"
                                        value={form.data.status}
                                        onChange={(e) =>
                                            form.setData(
                                                'status',
                                                e.target.value,
                                            )
                                        }
                                    >
                                        {statusOptions.map((option) => (
                                            <option
                                                key={option.value}
                                                value={option.value}
                                            >
                                                {option.label}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                                <div>
                                    <Label htmlFor="delivery_person_name">
                                        Delivery person
                                    </Label>
                                    <Input
                                        id="delivery_person_name"
                                        className="mt-2"
                                        value={form.data.delivery_person_name}
                                        onChange={(e) =>
                                            form.setData(
                                                'delivery_person_name',
                                                e.target.value,
                                            )
                                        }
                                    />
                                </div>
                                <div>
                                    <Label htmlFor="note">Note</Label>
                                    <textarea
                                        id="note"
                                        className="border-input mt-2 flex min-h-20 w-full rounded-md border bg-background px-3 py-2 text-sm"
                                        value={form.data.note}
                                        onChange={(e) =>
                                            form.setData(
                                                'note',
                                                e.target.value,
                                            )
                                        }
                                    />
                                </div>
                                <Button
                                    type="submit"
                                    disabled={form.processing}
                                >
                                    Save status
                                </Button>
                            </div>
                        </form>
                    )}
                </div>
            </div>
        </>
    );
}

function Detail({
    label,
    value,
    hint,
    emphasize = false,
}: {
    label: string;
    value: string;
    hint?: string;
    emphasize?: boolean;
}) {
    return (
        <div>
            <p className="text-[11px] font-bold tracking-[0.08em] text-muted-foreground uppercase">
                {label}
            </p>
            <p
                className={
                    emphasize
                        ? 'mt-1 font-display text-lg font-semibold'
                        : 'mt-1 text-sm font-medium whitespace-pre-wrap'
                }
            >
                {value}
            </p>
            {hint && (
                <p className="mt-1 text-xs text-muted-foreground">{hint}</p>
            )}
        </div>
    );
}

OrdersShow.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Orders', href: '/admin/orders' },
        { title: 'Details', href: '#' },
    ],
};
