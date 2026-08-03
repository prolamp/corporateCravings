import { Head, Link } from '@inertiajs/react';
import { CustomerNav } from '@/components/customer/customer-nav';
import {
    CustomerOrderCard,
    type CustomerOrderSummary,
} from '@/components/customer/order-card';
import { Pagination, type PaginationMeta } from '@/components/pagination';
import { Button } from '@/components/ui/button';

type Props = {
    orders: {
        data: CustomerOrderSummary[];
        meta: PaginationMeta;
    };
};

export default function CustomerOrders({ orders }: Props) {
    function pageUrl(page: number): string {
        return page > 1 ? `/customer/orders?page=${page}` : '/customer/orders';
    }

    return (
        <>
            <Head title="Order history" />

            <div className="mx-auto max-w-5xl px-4 py-10 md:px-10 md:py-16">
                <header className="mb-6 md:mb-8">
                    <h1 className="font-display text-4xl font-bold tracking-tight md:text-5xl">
                        Order history
                    </h1>
                    <p className="mt-3 max-w-xl text-muted-foreground">
                        All of your past and current orders. Reorder anytime.
                    </p>
                </header>

                <CustomerNav current="/customer/orders" />

                {orders.data.length === 0 ? (
                    <div className="rounded-2xl border border-dashed border-border/80 px-6 py-12 text-center">
                        <p className="text-muted-foreground">No orders yet.</p>
                        <Button asChild className="mt-4">
                            <Link href="/menu">Browse menu</Link>
                        </Button>
                    </div>
                ) : (
                    <>
                        <ul className="space-y-3">
                            {orders.data.map((order) => (
                                <CustomerOrderCard
                                    key={order.id}
                                    order={order}
                                />
                            ))}
                        </ul>
                        <div className="mt-8">
                            <Pagination meta={orders.meta} pageUrl={pageUrl} />
                        </div>
                    </>
                )}
            </div>
        </>
    );
}
