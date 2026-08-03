import { Link } from '@inertiajs/react';
import { CheckCircle2 } from 'lucide-react';
import { useEffect } from 'react';
import { SeoHead } from '@/components/public/seo-head';
import { StatusBadge } from '@/components/status-badge/status-badge';
import { Button } from '@/components/ui/button';
import { trackPurchase } from '@/lib/analytics';

type OrderItemAddon = {
    id: number;
    title: string;
    quantity: number;
    unit_price_formatted: string;
    line_total_formatted: string;
};

type OrderItem = {
    id: number;
    title: string;
    variation_label: string;
    quantity: number;
    unit_price_formatted: string;
    line_total_formatted: string;
    addons: OrderItemAddon[];
};

type Props = {
    order: {
        order_number: string;
        status: string;
        status_label: string;
        status_badge: string;
        payment_method: string;
        payment_label: string;
        customer_name: string;
        customer_phone: string | null;
        customer_email: string | null;
        delivery_address: string | null;
        delivery_zone: string | null;
        notes: string | null;
        total: number;
        total_formatted: string;
        items_count: number;
        items: OrderItem[];
        placed_at: string | null;
        placed_at_formatted: string | null;
    };
};

export default function OrderSuccess({ order }: Props) {
    useEffect(() => {
        trackPurchase({
            items_count: order.items_count,
            value: order.total,
        });
    }, [order.items_count, order.total]);

    return (
        <>
            <SeoHead title="Order confirmed" noindex />

            <div className="mx-auto max-w-3xl px-4 py-10 md:px-10 md:py-16">
                <div className="overflow-hidden rounded-2xl border border-border/80 bg-card shadow-sm">
                    <div className="border-b border-border/80 px-6 py-8 text-center md:px-10">
                        <div className="mx-auto flex size-14 items-center justify-center rounded-full bg-primary/15 text-primary">
                            <CheckCircle2 className="size-8" />
                        </div>
                        <h1 className="mt-5 font-display text-3xl font-bold tracking-tight md:text-4xl">
                            Order confirmed
                        </h1>
                        <p className="mt-2 text-muted-foreground">
                            Thanks {order.customer_name}. Your COD order is
                            pending confirmation.
                        </p>
                        <div className="mt-4 flex justify-center">
                            <StatusBadge
                                label={order.status_label}
                                badge={order.status_badge}
                                className="text-sm"
                            />
                        </div>
                        <p className="mt-5 text-[11px] font-bold tracking-[0.08em] text-muted-foreground uppercase">
                            Order number
                        </p>
                        <p className="mt-1 font-display text-2xl font-bold text-primary">
                            {order.order_number}
                        </p>
                        {order.placed_at_formatted && (
                            <p className="mt-2 text-sm text-muted-foreground">
                                {order.placed_at_formatted}
                            </p>
                        )}
                    </div>

                    <section className="border-t border-border/80 px-6 py-8 md:px-10">
                        <div className="flex items-baseline justify-between gap-3">
                            <h2 className="text-[11px] font-bold tracking-[0.08em] text-muted-foreground uppercase">
                                Order items
                            </h2>
                            <p className="text-sm text-muted-foreground">
                                {order.items_count}{' '}
                                {order.items_count === 1 ? 'line' : 'lines'}
                            </p>
                        </div>

                        <ul className="mt-5 divide-y divide-border/70">
                            {order.items.map((item) => (
                                <li
                                    key={item.id}
                                    className="flex items-start justify-between gap-4 py-4 first:pt-0 last:pb-0"
                                >
                                    <div className="min-w-0 flex-1">
                                        <p className="font-semibold tracking-tight">
                                            {item.title}
                                        </p>
                                        <p className="mt-1 text-sm text-muted-foreground">
                                            {item.variation_label} ·{' '}
                                            {item.unit_price_formatted} ×{' '}
                                            {item.quantity}
                                        </p>
                                        {item.addons.length > 0 && (
                                            <ul className="mt-2 space-y-1">
                                                {item.addons.map((addon) => (
                                                    <li
                                                        key={addon.id}
                                                        className="flex justify-between gap-3 text-sm text-muted-foreground"
                                                    >
                                                        <span>
                                                            + {addon.title}
                                                            {addon.quantity > 1
                                                                ? ` × ${addon.quantity}`
                                                                : ''}
                                                        </span>
                                                        <span className="shrink-0">
                                                            {
                                                                addon.line_total_formatted
                                                            }
                                                        </span>
                                                    </li>
                                                ))}
                                            </ul>
                                        )}
                                    </div>
                                    <p className="shrink-0 font-semibold tabular-nums">
                                        {item.line_total_formatted}
                                    </p>
                                </li>
                            ))}
                        </ul>
                    </section>

                    <div className="grid gap-8 border-t border-border/80 px-6 py-8 md:grid-cols-2 md:px-10">
                        <section>
                            <h2 className="text-[11px] font-bold tracking-[0.08em] text-muted-foreground uppercase">
                                Delivery address
                            </h2>
                            <div className="mt-3 space-y-1 text-sm">
                                <p className="font-semibold">{order.customer_name}</p>
                                {order.customer_phone && (
                                    <p className="text-muted-foreground">
                                        {order.customer_phone}
                                    </p>
                                )}
                                {order.customer_email && (
                                    <p className="text-muted-foreground">
                                        {order.customer_email}
                                    </p>
                                )}
                                {order.delivery_address && (
                                    <p className="mt-2 leading-relaxed">
                                        {order.delivery_address}
                                    </p>
                                )}
                                {order.delivery_zone && (
                                    <p className="text-muted-foreground">
                                        Zone: {order.delivery_zone}
                                    </p>
                                )}
                            </div>
                        </section>

                        <section>
                            <h2 className="text-[11px] font-bold tracking-[0.08em] text-muted-foreground uppercase">
                                Payment
                            </h2>
                            <div className="mt-3 space-y-1 text-sm">
                                <p className="font-semibold">{order.payment_label}</p>
                                <p className="text-muted-foreground">
                                    Pay when your order arrives
                                </p>
                                {order.notes && (
                                    <p className="mt-3 rounded-lg bg-muted/60 px-3 py-2 text-muted-foreground">
                                        Note: {order.notes}
                                    </p>
                                )}
                            </div>
                        </section>
                    </div>

                    <section className="border-t border-border/80 bg-muted/30 px-6 py-6 md:px-10">
                        <dl className="ml-auto max-w-sm text-sm">
                            <TotalsRow
                                label="Total"
                                value={order.total_formatted}
                                emphasize
                            />
                        </dl>
                    </section>

                    <div className="flex flex-col gap-3 border-t border-border/80 px-6 py-8 sm:flex-row sm:justify-center md:px-10">
                        <Button asChild size="lg" className="font-bold">
                            <Link
                                href={`/track-order?order=${encodeURIComponent(order.order_number)}`}
                            >
                                Track order
                            </Link>
                        </Button>
                        <Button asChild size="lg" variant="outline">
                            <Link href="/menu">Continue shopping</Link>
                        </Button>
                    </div>
                </div>
            </div>
        </>
    );
}

function TotalsRow({
    label,
    value,
    emphasize = false,
}: {
    label: string;
    value: string;
    emphasize?: boolean;
}) {
    return (
        <div className="flex items-center justify-between gap-4">
            <dt
                className={
                    emphasize
                        ? 'font-display text-base font-semibold'
                        : 'text-muted-foreground'
                }
            >
                {label}
            </dt>
            <dd
                className={
                    emphasize
                        ? 'font-display text-xl font-bold text-primary tabular-nums'
                        : 'font-medium tabular-nums'
                }
            >
                {value}
            </dd>
        </div>
    );
}
