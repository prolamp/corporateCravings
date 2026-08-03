import { Link, router } from '@inertiajs/react';
import { StatusBadge } from '@/components/status-badge/status-badge';
import { Button } from '@/components/ui/button';

export type CustomerOrderSummary = {
    id: number;
    order_number: string;
    status: string;
    status_label: string;
    status_badge: string;
    items_count: number;
    total_formatted: string;
    delivery_zone: string | null;
    placed_at_formatted: string | null;
    can_reorder: boolean;
    track_href: string;
};

export function CustomerOrderCard({ order }: { order: CustomerOrderSummary }) {
    function reorder() {
        router.post(`/customer/orders/${order.id}/reorder`);
    }

    return (
        <li className="rounded-2xl border border-border/80 bg-card p-4 md:p-5">
            <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div className="min-w-0">
                    <div className="flex flex-wrap items-center gap-2">
                        <p className="font-display text-lg font-semibold text-primary">
                            {order.order_number}
                        </p>
                        <StatusBadge
                            label={order.status_label}
                            badge={order.status_badge}
                        />
                    </div>
                    <p className="mt-1 text-sm text-muted-foreground">
                        {order.placed_at_formatted ?? '—'}
                        {order.delivery_zone ? ` · ${order.delivery_zone}` : ''}
                        {` · ${order.items_count} ${order.items_count === 1 ? 'line' : 'lines'}`}
                    </p>
                    <p className="mt-2 font-semibold tabular-nums">
                        {order.total_formatted}
                    </p>
                </div>

                <div className="flex flex-wrap gap-2">
                    <Button asChild size="sm" variant="outline">
                        <Link href={order.track_href}>Track</Link>
                    </Button>
                    {order.can_reorder && (
                        <Button size="sm" onClick={reorder}>
                            Reorder
                        </Button>
                    )}
                </div>
            </div>
        </li>
    );
}
