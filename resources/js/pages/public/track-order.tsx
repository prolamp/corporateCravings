import { router, usePage } from '@inertiajs/react';
import { SeoHead } from '@/components/public/seo-head';
import { Check, Circle, PackageSearch, X } from 'lucide-react';
import { FormEvent, useState } from 'react';
import InputError from '@/components/input-error';
import { StatusBadge } from '@/components/status-badge/status-badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';

type ProgressStep = {
    code: string;
    label: string;
    state: 'done' | 'current' | 'upcoming' | 'cancelled';
};

type TimelineRow = {
    id: number;
    status: string;
    status_label: string;
    status_badge: string;
    at_formatted: string | null;
};

type OrderItemAddon = {
    id: number;
    title: string;
    quantity: number;
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

type TrackedOrder = {
    order_number: string;
    status: string;
    status_label: string;
    status_badge: string;
    payment_label: string;
    customer_name: string;
    customer_phone: string | null;
    delivery_address: string | null;
    delivery_zone: string | null;
    total_formatted: string;
    placed_at_formatted: string | null;
    items: OrderItem[];
    progress: ProgressStep[];
    timeline: TimelineRow[];
};

type Props = {
    query: string;
    error: string | null;
    order: TrackedOrder | null;
};

export default function TrackOrder({ query, error, order }: Props) {
    const { site } = usePage().props;
    const [orderNumber, setOrderNumber] = useState(query);
    const [clientError, setClientError] = useState('');

    function submit(event: FormEvent) {
        event.preventDefault();
        const value = orderNumber.trim();
        if (!value) {
            setClientError('Enter your order number.');
            return;
        }

        setClientError('');
        router.get(
            '/track-order',
            { order: value },
            { preserveState: true, replace: true },
        );
    }

    return (
        <>
            <SeoHead
                title="Track order"
                description="Track your Corporate Cravings cash-on-delivery order with your order number."
                path="/track-order"
                noindex
            />

            <div className="mx-auto max-w-3xl px-4 py-10 md:px-10 md:py-16">
                <header className="mb-8 md:mb-10">
                    <p className="text-[11px] font-bold tracking-[0.08em] text-primary uppercase">
                        Orders
                    </p>
                    <h1 className="mt-2 font-display text-4xl font-bold tracking-tight md:text-5xl">
                        Track order
                    </h1>
                    <p className="mt-3 max-w-xl text-muted-foreground">
                        Enter the order number from your confirmation to see
                        status and delivery progress.
                    </p>
                </header>

                <form
                    onSubmit={submit}
                    noValidate
                    className="rounded-2xl border border-border/80 bg-card p-6 md:p-8"
                >
                    <Label htmlFor="track-order-number">Order number</Label>
                    <div className="mt-2 flex flex-col gap-3 sm:flex-row">
                        <Input
                            id="track-order-number"
                            value={orderNumber}
                            onChange={(e) => {
                                setOrderNumber(e.target.value);
                                if (clientError) {
                                    setClientError('');
                                }
                            }}
                            placeholder="e.g. CC-W-XXXXXXXX"
                            autoComplete="off"
                            className="sm:flex-1"
                        />
                        <Button type="submit" size="lg" className="font-bold sm:px-8">
                            Track
                        </Button>
                    </div>
                    <InputError
                        message={clientError || error || undefined}
                        className="mt-2"
                    />
                </form>

                {!order && !error && !query && (
                    <div className="mt-10 rounded-2xl border border-dashed border-border/80 px-6 py-12 text-center">
                        <PackageSearch className="mx-auto size-10 text-muted-foreground" />
                        <p className="mt-4 text-muted-foreground">
                            Your tracking details will appear here.
                        </p>
                        {site?.whatsapp_url && (
                            <p className="mt-2 text-sm text-muted-foreground">
                                Need help?{' '}
                                <a
                                    href={site.whatsapp_url}
                                    target="_blank"
                                    rel="noreferrer"
                                    className="font-medium text-primary underline-offset-4 hover:underline"
                                >
                                    WhatsApp us
                                </a>
                            </p>
                        )}
                    </div>
                )}

                {order && <OrderResult order={order} />}
            </div>
        </>
    );
}

function OrderResult({ order }: { order: TrackedOrder }) {
    return (
        <div className="mt-8 overflow-hidden rounded-2xl border border-border/80 bg-card shadow-sm">
            <div className="border-b border-border/80 px-6 py-6 md:px-8">
                <div className="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p className="text-[11px] font-bold tracking-[0.08em] text-muted-foreground uppercase">
                            Order number
                        </p>
                        <p className="mt-1 font-display text-2xl font-bold text-primary">
                            {order.order_number}
                        </p>
                        {order.placed_at_formatted && (
                            <p className="mt-1 text-sm text-muted-foreground">
                                Placed {order.placed_at_formatted}
                            </p>
                        )}
                    </div>
                    <StatusBadge
                        label={order.status_label}
                        badge={order.status_badge}
                        className="text-sm"
                    />
                </div>

                <ol className="mt-8 grid gap-3 sm:grid-cols-5">
                    {order.progress.map((step) => (
                        <li
                            key={step.code}
                            className="flex flex-col items-center text-center"
                        >
                            <span
                                className={cn(
                                    'flex size-9 items-center justify-center rounded-full border-2',
                                    step.state === 'done' &&
                                        'border-primary bg-primary text-primary-foreground',
                                    step.state === 'current' &&
                                        'border-primary bg-primary/15 text-primary',
                                    step.state === 'upcoming' &&
                                        'border-border text-muted-foreground',
                                    step.state === 'cancelled' &&
                                        'border-destructive bg-destructive/10 text-destructive',
                                )}
                            >
                                {step.state === 'done' ? (
                                    <Check className="size-4" />
                                ) : step.state === 'cancelled' ? (
                                    <X className="size-4" />
                                ) : (
                                    <Circle
                                        className={cn(
                                            'size-3',
                                            step.state === 'current' && 'fill-current',
                                        )}
                                    />
                                )}
                            </span>
                            <span
                                className={cn(
                                    'mt-2 text-xs font-medium',
                                    step.state === 'current' || step.state === 'done'
                                        ? 'text-foreground'
                                        : step.state === 'cancelled'
                                          ? 'text-destructive'
                                          : 'text-muted-foreground',
                                )}
                            >
                                {step.label}
                            </span>
                        </li>
                    ))}
                </ol>
            </div>

            {order.timeline.length > 0 && (
                <section className="border-b border-border/80 px-6 py-6 md:px-8">
                    <h2 className="text-[11px] font-bold tracking-[0.08em] text-muted-foreground uppercase">
                        Status history
                    </h2>
                    <ol className="mt-4 space-y-4">
                        {order.timeline.map((row, index) => (
                            <li key={row.id} className="flex gap-3">
                                <div className="flex flex-col items-center">
                                    <span
                                        className={cn(
                                            'mt-1 size-2.5 rounded-full',
                                            index === order.timeline.length - 1
                                                ? 'bg-primary'
                                                : 'bg-muted-foreground/40',
                                        )}
                                    />
                                    {index < order.timeline.length - 1 && (
                                        <span className="mt-1 w-px flex-1 bg-border" />
                                    )}
                                </div>
                                <div className="pb-1">
                                    <p className="font-medium">{row.status_label}</p>
                                    {row.at_formatted && (
                                        <p className="text-sm text-muted-foreground">
                                            {row.at_formatted}
                                        </p>
                                    )}
                                </div>
                            </li>
                        ))}
                    </ol>
                </section>
            )}

            <section className="border-b border-border/80 px-6 py-6 md:px-8">
                <h2 className="text-[11px] font-bold tracking-[0.08em] text-muted-foreground uppercase">
                    Items
                </h2>
                <ul className="mt-4 divide-y divide-border/70">
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
                                    {item.unit_price_formatted} × {item.quantity}
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
                                                    {addon.line_total_formatted}
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

            <div className="grid gap-6 px-6 py-6 md:grid-cols-2 md:px-8">
                <section>
                    <h2 className="text-[11px] font-bold tracking-[0.08em] text-muted-foreground uppercase">
                        Delivery
                    </h2>
                    <div className="mt-3 space-y-1 text-sm">
                        <p className="font-semibold">{order.customer_name}</p>
                        {order.customer_phone && (
                            <p className="text-muted-foreground">
                                {order.customer_phone}
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
                        <p className="mt-4 text-[11px] font-bold tracking-[0.08em] text-muted-foreground uppercase">
                            Total
                        </p>
                        <p className="font-display text-2xl font-bold text-primary">
                            {order.total_formatted}
                        </p>
                    </div>
                </section>
            </div>
        </div>
    );
}
