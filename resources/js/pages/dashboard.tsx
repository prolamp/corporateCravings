import { Head } from '@inertiajs/react';
import { StatusBadge } from '@/components/status-badge/status-badge';
import { formatCount, formatMoney } from '@/lib/money';
import { dashboard } from '@/routes';
import { cn } from '@/lib/utils';

type ScheduleRow = {
    id: number;
    company: string;
    dish_title: string | null;
    package_title: string | null;
    persons: number;
    status: string;
    status_label: string;
    status_badge: string;
};

type OrderRow = {
    id: number;
    order_number: string;
    customer_name: string | null;
    customer_phone: string | null;
    type: string;
    channel: string;
    status: string;
    status_label: string;
    status_badge: string;
    quoted_amount: number;
    placed_at: string | null;
};

type DashboardProps = {
    widgets: string[];
    stats: Record<string, number>;
    recentOrders: OrderRow[];
    todaysSchedule: ScheduleRow[];
    canViewEarnings: boolean;
    canViewProfitLoss: boolean;
    canViewPayments: boolean;
};

function hasWidget(widgets: string[], key: string): boolean {
    return widgets.includes(key);
}

export default function Dashboard({
    widgets,
    stats,
    recentOrders,
    todaysSchedule,
    canViewEarnings,
    canViewProfitLoss,
}: DashboardProps) {
    const showFinance =
        canViewEarnings ||
        canViewProfitLoss ||
        hasWidget(widgets, 'earnings') ||
        hasWidget(widgets, 'expenses');

    return (
        <>
            <Head title="Dashboard" />
            <div className="flex flex-1 flex-col gap-6 overflow-x-auto p-4 md:p-6">
                <div>
                    <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                        Operations overview
                    </p>
                    <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight md:text-3xl">
                        Today at Corporate Cravings
                    </h2>
                    <p className="mt-1 max-w-2xl text-sm text-muted-foreground">
                        Live counts for orders, subscriber deliveries, and
                        pending actions. Finance totals respect your role.
                    </p>
                </div>

                {showFinance && (
                    <section className="space-y-3">
                        <div className="flex flex-wrap items-center gap-2">
                            <h3 className="font-display text-lg font-semibold">
                                Finance overview
                            </h3>
                            {(canViewEarnings || canViewProfitLoss) && (
                                <span className="rounded bg-secondary-container px-2 py-0.5 text-[10px] font-bold tracking-wider text-on-secondary-container uppercase">
                                    Authorized view
                                </span>
                            )}
                        </div>
                        <div className="grid gap-4 md:grid-cols-3">
                            {hasWidget(widgets, 'earnings') && (
                                <KpiCard
                                    label="Today's revenue"
                                    value={formatMoney(stats.earnings_today)}
                                    hint={`Paid total ${formatMoney(stats.earnings)}`}
                                    accent="primary"
                                />
                            )}
                            {hasWidget(widgets, 'expenses') && (
                                <KpiCard
                                    label="Month-to-date expenses"
                                    value={formatMoney(stats.expenses_mtd)}
                                    hint={`All-time ${formatMoney(stats.expenses)}`}
                                />
                            )}
                            {canViewProfitLoss &&
                                hasWidget(widgets, 'earnings') &&
                                hasWidget(widgets, 'expenses') && (
                                    <KpiCard
                                        label="Estimated profit MTD"
                                        value={formatMoney(stats.profit_mtd)}
                                        hint="Paid revenue minus expenses this month"
                                        accent="profit"
                                    />
                                )}
                        </div>
                        {!canViewEarnings && (
                            <p className="text-xs text-muted-foreground">
                                Earnings and profit &amp; loss are hidden for
                                your role.
                            </p>
                        )}
                    </section>
                )}

                <section className="space-y-3">
                    <h3 className="font-display text-lg font-semibold">
                        Operations hub
                    </h3>
                    <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        {hasWidget(widgets, 'orders') && (
                            <KpiCard
                                label="Orders today"
                                value={formatCount(stats.orders_today)}
                                hint={`All time ${formatCount(stats.orders)}`}
                            />
                        )}
                        {hasWidget(widgets, 'pending_deliveries') && (
                            <KpiCard
                                label="Pending deliveries"
                                value={formatCount(stats.pending_deliveries)}
                                hint={`${formatCount(stats.subscriber_deliveries_today)} subscriber lunches today`}
                            />
                        )}
                        {hasWidget(widgets, 'corporate_contracts') && (
                            <KpiCard
                                label="Active contracts"
                                value={formatCount(stats.corporate_contracts)}
                            />
                        )}
                        {hasWidget(widgets, 'customers') && (
                            <KpiCard
                                label="Customers"
                                value={formatCount(stats.customers)}
                            />
                        )}
                    </div>
                </section>

                <div className="grid gap-6 xl:grid-cols-12">
                    <section className="rounded-xl border border-border/80 bg-card xl:col-span-5">
                        <div className="border-b border-border/60 px-4 py-3 md:px-5">
                            <h3 className="font-display text-base font-semibold">
                                Today&apos;s subscriber schedule
                            </h3>
                            <p className="text-xs text-muted-foreground">
                                Lunch deliveries planned for today
                            </p>
                        </div>
                        {todaysSchedule.length === 0 ? (
                            <EmptyState message="No subscriber deliveries scheduled for today." />
                        ) : (
                            <ul className="divide-y divide-border/60">
                                {todaysSchedule.map((row) => (
                                    <li
                                        key={row.id}
                                        className="flex items-start justify-between gap-3 px-4 py-3 md:px-5"
                                    >
                                        <div className="min-w-0">
                                            <p className="truncate text-sm font-semibold">
                                                {row.company}
                                            </p>
                                            <p className="truncate text-xs text-muted-foreground">
                                                {row.dish_title ||
                                                    row.package_title ||
                                                    'Scheduled meal'}{' '}
                                                · {row.persons} persons
                                            </p>
                                        </div>
                                        <StatusBadge
                                            label={row.status_label}
                                            badge={row.status_badge}
                                        />
                                    </li>
                                ))}
                            </ul>
                        )}
                    </section>

                    <section className="rounded-xl border border-border/80 bg-card xl:col-span-7">
                        <div className="border-b border-border/60 px-4 py-3 md:px-5">
                            <h3 className="font-display text-base font-semibold">
                                Recent orders
                            </h3>
                            <p className="text-xs text-muted-foreground">
                                Latest activity across all channels
                            </p>
                        </div>
                        {recentOrders.length === 0 ? (
                            <EmptyState message="No orders yet. Seeded demo data will appear after checkout or admin entry." />
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="w-full min-w-[560px] text-left text-sm">
                                    <thead className="bg-muted/40 text-[11px] font-bold tracking-wider text-muted-foreground uppercase">
                                        <tr>
                                            <th className="px-4 py-2.5 font-bold md:px-5">
                                                Order
                                            </th>
                                            <th className="px-4 py-2.5 font-bold">
                                                Customer
                                            </th>
                                            <th className="px-4 py-2.5 font-bold">
                                                Status
                                            </th>
                                            <th className="px-4 py-2.5 text-right font-bold md:px-5">
                                                Amount
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-border/60">
                                        {recentOrders.map((order) => (
                                            <tr key={order.id}>
                                                <td className="px-4 py-3 md:px-5">
                                                    <p className="font-medium">
                                                        {order.order_number}
                                                    </p>
                                                    <p className="text-xs text-muted-foreground capitalize">
                                                        {order.type} ·{' '}
                                                        {order.channel.replace(
                                                            '_',
                                                            ' ',
                                                        )}
                                                    </p>
                                                </td>
                                                <td className="px-4 py-3">
                                                    <p className="font-medium">
                                                        {order.customer_name ||
                                                            '—'}
                                                    </p>
                                                    <p className="text-xs text-muted-foreground">
                                                        {order.customer_phone ||
                                                            '—'}
                                                    </p>
                                                </td>
                                                <td className="px-4 py-3">
                                                    <StatusBadge
                                                        label={
                                                            order.status_label
                                                        }
                                                        badge={
                                                            order.status_badge
                                                        }
                                                    />
                                                </td>
                                                <td className="px-4 py-3 text-right font-medium md:px-5">
                                                    {formatMoney(
                                                        order.quoted_amount,
                                                    )}
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        )}
                    </section>
                </div>
            </div>
        </>
    );
}

function KpiCard({
    label,
    value,
    hint,
    accent,
}: {
    label: string;
    value: string;
    hint?: string;
    accent?: 'primary' | 'profit';
}) {
    return (
        <div
            className={cn(
                'rounded-xl border border-border/80 bg-card p-4 md:p-5',
                accent === 'primary' &&
                    'border-primary/20 bg-primary-container/15',
                accent === 'profit' &&
                    'border-primary-container/40 bg-secondary-container/40 dark:bg-primary-container/20',
            )}
        >
            <p className="text-[11px] font-bold tracking-[0.08em] text-muted-foreground uppercase">
                {label}
            </p>
            <p className="mt-2 font-display text-2xl font-semibold tracking-tight md:text-3xl">
                {value}
            </p>
            {hint && (
                <p className="mt-2 text-xs text-muted-foreground">{hint}</p>
            )}
        </div>
    );
}

function EmptyState({ message }: { message: string }) {
    return (
        <div className="px-4 py-10 text-center text-sm text-muted-foreground md:px-5">
            {message}
        </div>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};
