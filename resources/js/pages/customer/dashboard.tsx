import { Head, Link } from '@inertiajs/react';
import { CustomerNav } from '@/components/customer/customer-nav';
import {
    CustomerOrderCard,
    type CustomerOrderSummary,
} from '@/components/customer/order-card';
import { Button } from '@/components/ui/button';

type Props = {
    stats: {
        my_orders_count: number;
        pending_count: number;
        completed_count: number;
    };
    recent_orders: CustomerOrderSummary[];
    links: {
        orders: boolean;
        track: boolean;
        history: boolean;
    };
};

export default function CustomerDashboard({
    stats,
    recent_orders,
    links,
}: Props) {
    return (
        <>
            <Head title="My account" />

            <div className="mx-auto max-w-5xl px-4 py-10 md:px-10 md:py-16">
                <header className="mb-6 md:mb-8">
                    <p className="text-[11px] font-bold tracking-[0.08em] text-primary uppercase">
                        Account
                    </p>
                    <h1 className="mt-2 font-display text-4xl font-bold tracking-tight md:text-5xl">
                        My orders
                    </h1>
                    <p className="mt-3 max-w-xl text-muted-foreground">
                        Track deliveries, review history, and reorder favorites.
                    </p>
                </header>

                <CustomerNav current="/customer/dashboard" />

                <div className="grid gap-4 sm:grid-cols-3">
                    <StatCard label="Total orders" value={stats.my_orders_count} />
                    <StatCard label="In progress" value={stats.pending_count} />
                    <StatCard label="Completed" value={stats.completed_count} />
                </div>

                <div className="mt-8 flex flex-wrap gap-3">
                    {links.track && (
                        <Button asChild variant="outline">
                            <Link href="/track-order">Track order</Link>
                        </Button>
                    )}
                    <Button asChild>
                        <Link href="/menu">Browse menu</Link>
                    </Button>
                </div>

                <section className="mt-10">
                    <div className="mb-4 flex items-end justify-between gap-3">
                        <h2 className="font-display text-2xl font-semibold tracking-tight">
                            Recent orders
                        </h2>
                        {(links.history || links.orders) &&
                            recent_orders.length > 0 && (
                                <Link
                                    href="/customer/orders"
                                    className="text-sm font-medium text-primary underline-offset-4 hover:underline"
                                >
                                    View all
                                </Link>
                            )}
                    </div>

                    {recent_orders.length === 0 ? (
                        <div className="rounded-2xl border border-dashed border-border/80 px-6 py-12 text-center">
                            <p className="text-muted-foreground">
                                No orders yet. Start with the menu.
                            </p>
                            <Button asChild className="mt-4">
                                <Link href="/menu">Browse menu</Link>
                            </Button>
                        </div>
                    ) : (
                        <ul className="space-y-3">
                            {recent_orders.map((order) => (
                                <CustomerOrderCard key={order.id} order={order} />
                            ))}
                        </ul>
                    )}
                </section>
            </div>
        </>
    );
}

function StatCard({ label, value }: { label: string; value: number }) {
    return (
        <div className="rounded-2xl border border-border/80 bg-card p-5">
            <p className="text-sm text-muted-foreground">{label}</p>
            <p className="mt-2 font-display text-3xl font-bold tabular-nums">
                {value.toLocaleString()}
            </p>
        </div>
    );
}
