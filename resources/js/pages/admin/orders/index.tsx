import { Head, Link, router } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { Pagination } from '@/components/pagination';
import { StatusBadge } from '@/components/status-badge/status-badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

export type StatusOption = { value: string; label: string };

export type AdminOrder = {
    id: number;
    order_number: string;
    type: string;
    type_label: string;
    channel: string;
    channel_label: string;
    status: string;
    status_label: string;
    status_badge: string;
    customer_name: string | null;
    customer_phone: string | null;
    quoted_amount_formatted: string;
    amount_paid_formatted: string | null;
    subscriber_company: string | null;
    items_count: number;
    placed_at_formatted: string | null;
    whatsapp_url: string | null;
};

type Paginator = {
    data: AdminOrder[];
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number | null;
        to: number | null;
    };
};

type Props = {
    orders: Paginator;
    filters: {
        search: string | null;
        type: string | null;
        channel: string | null;
        status: string | null;
        date_from: string | null;
        date_to: string | null;
    };
    statusOptions: StatusOption[];
    typeOptions: StatusOption[];
    channelOptions: StatusOption[];
    canUpdateStatus: boolean;
    canManage: boolean;
};

export default function OrdersIndex({
    orders,
    filters,
    statusOptions,
    typeOptions,
    channelOptions,
    canManage,
}: Props) {
    function applyFilters(next: Partial<Props['filters']>) {
        router.get(
            '/admin/orders',
            {
                search: next.search ?? filters.search ?? '',
                type: next.type ?? filters.type ?? '',
                channel: next.channel ?? filters.channel ?? '',
                status: next.status ?? filters.status ?? '',
                date_from: next.date_from ?? filters.date_from ?? '',
                date_to: next.date_to ?? filters.date_to ?? '',
            },
            { preserveState: true, replace: true },
        );
    }

    function pageUrl(page: number): string {
        const params = new URLSearchParams();
        if (filters.search) params.set('search', filters.search);
        if (filters.type) params.set('type', filters.type);
        if (filters.channel) params.set('channel', filters.channel);
        if (filters.status) params.set('status', filters.status);
        if (filters.date_from) params.set('date_from', filters.date_from);
        if (filters.date_to) params.set('date_to', filters.date_to);
        if (page > 1) params.set('page', String(page));
        const query = params.toString();

        return query ? `/admin/orders?${query}` : '/admin/orders';
    }

    return (
        <>
            <Head title="Orders" />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                            Orders
                        </p>
                        <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight md:text-3xl">
                            Unified orders
                        </h2>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Menu COD, subscription deliveries, and bulk/event
                            orders in one place.
                        </p>
                    </div>
                    {canManage && (
                        <Button asChild>
                            <Link href="/admin/orders/create">
                                <Plus className="size-4" />
                                Create order
                            </Link>
                        </Button>
                    )}
                </div>

                <div className="grid gap-3 rounded-xl border border-border/80 bg-card p-4 md:grid-cols-3 lg:grid-cols-4">
                    <Input
                        key={`q-${filters.search ?? ''}`}
                        placeholder="Search #, name, phone…"
                        defaultValue={filters.search ?? ''}
                        onKeyDown={(e) => {
                            if (e.key === 'Enter') {
                                applyFilters({
                                    search: (
                                        e.target as HTMLInputElement
                                    ).value,
                                });
                            }
                        }}
                    />
                    <select
                        className={selectClass}
                        value={filters.type ?? ''}
                        onChange={(e) =>
                            applyFilters({ type: e.target.value || null })
                        }
                    >
                        <option value="">All types</option>
                        {typeOptions.map((option) => (
                            <option key={option.value} value={option.value}>
                                {option.label}
                            </option>
                        ))}
                    </select>
                    <select
                        className={selectClass}
                        value={filters.channel ?? ''}
                        onChange={(e) =>
                            applyFilters({ channel: e.target.value || null })
                        }
                    >
                        <option value="">All channels</option>
                        {channelOptions.map((option) => (
                            <option key={option.value} value={option.value}>
                                {option.label}
                            </option>
                        ))}
                    </select>
                    <select
                        className={selectClass}
                        value={filters.status ?? ''}
                        onChange={(e) =>
                            applyFilters({ status: e.target.value || null })
                        }
                    >
                        <option value="">All statuses</option>
                        {statusOptions.map((option) => (
                            <option key={option.value} value={option.value}>
                                {option.label}
                            </option>
                        ))}
                    </select>
                    <Input
                        type="date"
                        value={filters.date_from ?? ''}
                        onChange={(e) =>
                            applyFilters({
                                date_from: e.target.value || null,
                            })
                        }
                    />
                    <Input
                        type="date"
                        value={filters.date_to ?? ''}
                        onChange={(e) =>
                            applyFilters({ date_to: e.target.value || null })
                        }
                    />
                    <Button
                        type="button"
                        variant="outline"
                        onClick={() =>
                            router.get('/admin/orders', {}, { replace: true })
                        }
                    >
                        Clear filters
                    </Button>
                </div>

                <div className="overflow-hidden rounded-xl border border-border/80">
                    <table className="w-full text-left text-sm">
                        <thead className="border-b bg-muted/40 text-[11px] font-bold tracking-[0.08em] text-muted-foreground uppercase">
                            <tr>
                                <th className="px-4 py-3">Order</th>
                                <th className="px-4 py-3">Customer</th>
                                <th className="px-4 py-3">Type</th>
                                <th className="px-4 py-3">Channel</th>
                                <th className="px-4 py-3">Total</th>
                                <th className="px-4 py-3">Status</th>
                                <th className="px-4 py-3 text-right">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {orders.data.length === 0 ? (
                                <tr>
                                    <td
                                        colSpan={7}
                                        className="px-4 py-10 text-center text-muted-foreground"
                                    >
                                        No orders match these filters.
                                    </td>
                                </tr>
                            ) : (
                                orders.data.map((order) => (
                                    <tr
                                        key={order.id}
                                        className="border-b last:border-0"
                                    >
                                        <td className="px-4 py-3">
                                            <Link
                                                href={`/admin/orders/${order.id}`}
                                                className="font-medium hover:underline"
                                            >
                                                {order.order_number}
                                            </Link>
                                            <p className="text-xs text-muted-foreground">
                                                {order.placed_at_formatted ||
                                                    '—'}
                                            </p>
                                        </td>
                                        <td className="px-4 py-3">
                                            <p className="font-medium">
                                                {order.subscriber_company ||
                                                    order.customer_name ||
                                                    '—'}
                                            </p>
                                            <p className="text-xs text-muted-foreground">
                                                {order.customer_phone || '—'}
                                            </p>
                                        </td>
                                        <td className="px-4 py-3">
                                            {order.type_label}
                                        </td>
                                        <td className="px-4 py-3">
                                            {order.channel_label}
                                        </td>
                                        <td className="px-4 py-3 font-medium">
                                            {order.quoted_amount_formatted}
                                        </td>
                                        <td className="px-4 py-3">
                                            <StatusBadge
                                                label={order.status_label}
                                                badge={order.status_badge}
                                            />
                                        </td>
                                        <td className="px-4 py-3 text-right">
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                asChild
                                            >
                                                <Link
                                                    href={`/admin/orders/${order.id}`}
                                                >
                                                    View
                                                </Link>
                                            </Button>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>

                <Pagination meta={orders.meta} pageUrl={pageUrl} />
            </div>
        </>
    );
}

const selectClass =
    'border-input flex h-9 w-full rounded-md border bg-background px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50';

OrdersIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Orders', href: '/admin/orders' },
    ],
};
