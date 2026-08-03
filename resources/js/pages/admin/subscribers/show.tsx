import { Head, Link } from '@inertiajs/react';
import { StatusBadge } from '@/components/status-badge/status-badge';
import { StatusToggle } from '@/components/status-toggle';
import { Button } from '@/components/ui/button';
import {
    destroySubscriber,
    type AdminSubscriber,
} from '@/pages/admin/subscribers/subscriber-form';

export type DailyBillRow = {
    order_id: number;
    order_number: string;
    date: string | null;
    date_formatted: string;
    day: string;
    day_full: string;
    persons: number;
    meal: string;
    meal_type: string | null;
    meal_type_label: string | null;
    total: number;
    total_formatted: string;
    delivered_at: string | null;
};

type Props = {
    subscriber: AdminSubscriber;
    dailyBills: DailyBillRow[];
    recentSlips: {
        id: number;
        slip_number: string;
        period_label: string;
        total_formatted: string;
        status_label: string;
        status_badge: string;
        items_count: number;
    }[];
    canManage: boolean;
    canManageBillSlips: boolean;
    createBillSlipUrl: string;
};

export default function SubscribersShow({
    subscriber,
    dailyBills,
    recentSlips,
    canManage,
    canManageBillSlips,
    createBillSlipUrl,
}: Props) {
    const title = subscriber.company_name || subscriber.name;
    const billsTotal = dailyBills.reduce((sum, row) => sum + row.total, 0);
    const billsTotalFormatted = `PKR ${billsTotal.toLocaleString('en-PK', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`;

    return (
        <>
            <Head title={title} />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div className="flex items-start gap-4">
                        {subscriber.image_url ? (
                            <img
                                src={subscriber.image_url}
                                alt=""
                                className="size-20 rounded-xl object-cover"
                            />
                        ) : (
                            <div className="flex size-20 items-center justify-center rounded-xl bg-muted font-display text-2xl font-semibold text-muted-foreground">
                                {title.slice(0, 1).toUpperCase()}
                            </div>
                        )}
                        <div>
                            <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                                Packages
                            </p>
                            <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight">
                                {title}
                            </h2>
                            <div className="mt-2 flex flex-wrap items-center gap-2">
                                <StatusBadge
                                    label={subscriber.status_label}
                                    badge={subscriber.status_badge}
                                />
                                <span className="text-sm text-muted-foreground">
                                    {subscriber.name}
                                    {subscriber.phone
                                        ? ` · ${subscriber.phone}`
                                        : ''}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div className="flex flex-wrap items-center gap-2">
                        <StatusToggle
                            endpoint={`/admin/subscribers/${subscriber.id}/status`}
                            status={subscriber.status}
                            canToggle={canManage}
                            activeLabel="Deactivate subscriber"
                            inactiveLabel="Activate subscriber"
                        />
                        <Button variant="outline" asChild>
                            <Link href="/admin/subscribers">Back</Link>
                        </Button>
                        {canManage && (
                            <Button asChild>
                                <Link
                                    href={`/admin/subscribers/${subscriber.id}/edit`}
                                >
                                    Edit
                                </Link>
                            </Button>
                        )}
                        {canManage &&
                            subscriber.can_delete &&
                            !subscriber.contract && (
                                <Button
                                    variant="destructive"
                                    type="button"
                                    onClick={() =>
                                        destroySubscriber(subscriber.id)
                                    }
                                >
                                    Delete
                                </Button>
                            )}
                    </div>
                </div>

                <div className="grid gap-4 rounded-xl border border-border/80 bg-card p-4 md:grid-cols-2 md:p-6">
                    <Detail label="Email" value={subscriber.email || '—'} />
                    <Detail label="Phone" value={subscriber.phone || '—'} />
                    <Detail
                        label="Address"
                        value={subscriber.address || '—'}
                    />
                    <Detail label="Contact" value={subscriber.name} />
                </div>

                <div className="rounded-xl border border-border/80 bg-card p-4 md:p-6">
                    <div className="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 className="font-display text-lg font-semibold tracking-tight">
                                Contract
                            </h3>
                            <p className="text-sm text-muted-foreground">
                                Private pricing — staff only
                            </p>
                        </div>
                        {canManage ? (
                            <Button variant="outline" asChild>
                                <Link
                                    href={
                                        subscriber.contract
                                            ? `/admin/contracts/${subscriber.contract.id}/edit`
                                            : `/admin/contracts/create?subscriber_id=${subscriber.id}`
                                    }
                                >
                                    {subscriber.contract
                                        ? 'Manage contract'
                                        : 'Add contract'}
                                </Link>
                            </Button>
                        ) : (
                            subscriber.contract && (
                                <Button variant="outline" asChild>
                                    <Link
                                        href={`/admin/contracts/${subscriber.contract.id}`}
                                    >
                                        View contract
                                    </Link>
                                </Button>
                            )
                        )}
                    </div>

                    {!subscriber.contract ? (
                        <p className="mt-4 text-sm text-muted-foreground">
                            No contract yet. Add private pricing when the
                            company buys a package.
                        </p>
                    ) : (
                        <div className="mt-4 grid gap-4 md:grid-cols-3">
                            <Detail
                                label="Package"
                                value={
                                    subscriber.contract.package_title || '—'
                                }
                            />
                            <Detail
                                label="Persons"
                                value={String(subscriber.contract.persons)}
                            />
                            <Detail
                                label="Status"
                                value={subscriber.contract.status_label}
                            />
                            {subscriber.contract.discount > 0 && (
                                <>
                                    <Detail
                                        label="Package rate /pp"
                                        value={
                                            subscriber.contract
                                                .package_amount_formatted
                                        }
                                    />
                                    <Detail
                                        label="Discount /pp"
                                        value={
                                            subscriber.contract
                                                .discount_formatted
                                        }
                                    />
                                </>
                            )}
                            <Detail
                                label="Contract rate /pp"
                                value={
                                    subscriber.contract
                                        .agreed_total_formatted
                                }
                            />
                            <div className="md:col-span-3">
                                <Button
                                    variant="link"
                                    className="h-auto p-0"
                                    asChild
                                >
                                    <Link
                                        href={`/admin/contracts/${subscriber.contract.id}`}
                                    >
                                        Open contract details
                                    </Link>
                                </Button>
                            </div>
                        </div>
                    )}
                </div>

                <div className="rounded-xl border border-border/80 bg-card p-4 md:p-6">
                    <div className="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <h3 className="font-display text-lg font-semibold tracking-tight">
                                Bill slips
                            </h3>
                            <p className="text-sm text-muted-foreground">
                                Generate receipts from delivered schedule days
                            </p>
                        </div>
                        {canManageBillSlips && (
                            <Button asChild>
                                <Link href={createBillSlipUrl}>
                                    Create bill slip
                                </Link>
                            </Button>
                        )}
                    </div>
                    {recentSlips.length === 0 ? (
                        <p className="mt-4 text-sm text-muted-foreground">
                            No bill slips yet for this subscriber.
                        </p>
                    ) : (
                        <ul className="mt-4 divide-y">
                            {recentSlips.map((slip) => (
                                <li
                                    key={slip.id}
                                    className="flex flex-wrap items-center justify-between gap-2 py-3"
                                >
                                    <div>
                                        <Link
                                            href={`/admin/bill-slips/${slip.id}`}
                                            className="font-medium hover:underline"
                                        >
                                            {slip.slip_number}
                                        </Link>
                                        <p className="text-sm text-muted-foreground">
                                            {slip.period_label} ·{' '}
                                            {slip.items_count} days ·{' '}
                                            {slip.total_formatted}
                                        </p>
                                    </div>
                                    <StatusBadge
                                        label={slip.status_label}
                                        badge={slip.status_badge}
                                    />
                                </li>
                            ))}
                        </ul>
                    )}
                    {recentSlips.length > 0 && (
                        <Button variant="link" className="mt-2 h-auto p-0" asChild>
                            <Link
                                href={`/admin/bill-slips?user_id=${subscriber.id}`}
                            >
                                View all bill slips
                            </Link>
                        </Button>
                    )}
                </div>

                <div className="rounded-xl border border-border/80 bg-card p-4 md:p-6">
                    <div className="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <h3 className="font-display text-lg font-semibold tracking-tight">
                                Daily bills
                            </h3>
                            <p className="text-sm text-muted-foreground">
                                Rows appear when a subscription order is marked
                                delivered
                            </p>
                        </div>
                        {dailyBills.length > 0 && (
                            <p className="text-sm font-medium">
                                Delivered total:{' '}
                                <span className="font-display text-base font-semibold">
                                    {billsTotalFormatted}
                                </span>
                            </p>
                        )}
                    </div>

                    {dailyBills.length === 0 ? (
                        <p className="mt-4 text-sm text-muted-foreground">
                            No delivered days yet. After schedules create daily
                            orders, marking each order as delivered will list
                            date, day, persons, meal, and total here.
                        </p>
                    ) : (
                        <div className="mt-4 overflow-x-auto">
                            <table className="w-full text-left text-sm">
                                <thead className="border-b text-[11px] font-bold tracking-[0.08em] text-muted-foreground uppercase">
                                    <tr>
                                        <th className="pb-2 pr-4">Date</th>
                                        <th className="pb-2 pr-4">Day</th>
                                        <th className="pb-2 pr-4">Persons</th>
                                        <th className="pb-2 pr-4">Meal</th>
                                        <th className="pb-2 text-right">
                                            Total
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {dailyBills.map((row) => (
                                        <tr
                                            key={row.order_id}
                                            className="border-b last:border-0"
                                        >
                                            <td className="py-2.5 pr-4 font-medium">
                                                {row.date_formatted}
                                            </td>
                                            <td className="py-2.5 pr-4 text-muted-foreground">
                                                {row.day_full}
                                            </td>
                                            <td className="py-2.5 pr-4">
                                                {row.persons}
                                            </td>
                                            <td className="py-2.5 pr-4">
                                                <span>{row.meal}</span>
                                                {row.meal_type_label && (
                                                    <span className="mt-0.5 block text-xs text-muted-foreground">
                                                        {row.meal_type_label}
                                                    </span>
                                                )}
                                            </td>
                                            <td className="py-2.5 text-right font-medium">
                                                {row.total_formatted}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}
                </div>
            </div>
        </>
    );
}

function Detail({ label, value }: { label: string; value: string }) {
    return (
        <div>
            <p className="text-[11px] font-bold tracking-[0.08em] text-muted-foreground uppercase">
                {label}
            </p>
            <p className="mt-1 text-sm font-medium">{value}</p>
        </div>
    );
}

SubscribersShow.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Subscribers', href: '/admin/subscribers' },
        { title: 'Details', href: '#' },
    ],
};
