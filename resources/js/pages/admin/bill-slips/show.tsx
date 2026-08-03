import { Head, Link, router } from '@inertiajs/react';
import { StatusBadge } from '@/components/status-badge/status-badge';
import { Button } from '@/components/ui/button';

type SlipItem = {
    id: number;
    date_formatted: string | null;
    dish_title: string | null;
    persons: number;
    unit_price_formatted: string;
    line_total_formatted: string;
};

type Slip = {
    id: number;
    slip_number: string;
    company_name: string | null;
    package_title: string | null;
    period_label: string;
    subtotal_formatted: string;
    adjustment: number;
    adjustment_formatted: string;
    total_formatted: string;
    status: string;
    status_label: string;
    status_badge: string;
    paid_at_formatted: string | null;
    notes: string | null;
    created_at_formatted: string | null;
    can_edit: boolean;
    can_mark_paid: boolean;
    can_mark_unpaid: boolean;
    can_cancel: boolean;
    items: SlipItem[];
};

type Props = {
    slip: Slip;
    canManage: boolean;
};

export default function BillSlipsShow({ slip, canManage }: Props) {
    function markPaid() {
        router.patch(`/admin/bill-slips/${slip.id}/mark-paid`, {}, {
            preserveScroll: true,
        });
    }

    function markUnpaid() {
        router.patch(`/admin/bill-slips/${slip.id}/mark-unpaid`, {}, {
            preserveScroll: true,
        });
    }

    function cancel() {
        if (!confirm('Cancel this bill slip? Schedule days will become available again.')) {
            return;
        }
        router.patch(`/admin/bill-slips/${slip.id}/cancel`);
    }

    return (
        <>
            <Head title={slip.slip_number} />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                            Packages
                        </p>
                        <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight">
                            {slip.slip_number}
                        </h2>
                        <div className="mt-2 flex flex-wrap items-center gap-2">
                            <StatusBadge
                                label={slip.status_label}
                                badge={slip.status_badge}
                            />
                            <span className="text-sm text-muted-foreground">
                                {slip.company_name}
                                {slip.package_title
                                    ? ` · ${slip.package_title}`
                                    : ''}
                            </span>
                        </div>
                    </div>
                    <div className="flex flex-wrap gap-2">
                        <Button variant="outline" asChild>
                            <Link
                                href={`/admin/bill-slips/${slip.id}/receipt`}
                                target="_blank"
                            >
                                Download receipt
                            </Link>
                        </Button>
                        {canManage && slip.can_edit && (
                            <Button variant="outline" asChild>
                                <Link href={`/admin/bill-slips/${slip.id}/edit`}>
                                    Edit
                                </Link>
                            </Button>
                        )}
                        {canManage && slip.can_mark_paid && (
                            <Button onClick={markPaid}>Mark paid</Button>
                        )}
                        {canManage && slip.can_mark_unpaid && (
                            <Button variant="outline" onClick={markUnpaid}>
                                Mark unpaid
                            </Button>
                        )}
                        {canManage && slip.can_cancel && (
                            <Button variant="destructive" onClick={cancel}>
                                Cancel slip
                            </Button>
                        )}
                    </div>
                </div>

                <div className="grid gap-4 sm:grid-cols-3">
                    <Detail label="Period" value={slip.period_label} />
                    <Detail label="Created" value={slip.created_at_formatted || '—'} />
                    <Detail
                        label="Paid at"
                        value={slip.paid_at_formatted || '—'}
                    />
                </div>

                <div className="overflow-x-auto rounded-xl border border-border/80 bg-card">
                    <table className="w-full text-left text-sm">
                        <thead className="border-b text-[11px] font-bold tracking-[0.08em] text-muted-foreground uppercase">
                            <tr>
                                <th className="px-4 py-3">Date</th>
                                <th className="px-4 py-3">Dish</th>
                                <th className="px-4 py-3">Persons</th>
                                <th className="px-4 py-3">Unit</th>
                                <th className="px-4 py-3 text-right">Line total</th>
                            </tr>
                        </thead>
                        <tbody>
                            {slip.items.map((item) => (
                                <tr
                                    key={item.id}
                                    className="border-b last:border-0"
                                >
                                    <td className="px-4 py-3 font-medium">
                                        {item.date_formatted}
                                    </td>
                                    <td className="px-4 py-3">{item.dish_title}</td>
                                    <td className="px-4 py-3">{item.persons}</td>
                                    <td className="px-4 py-3">
                                        {item.unit_price_formatted}
                                    </td>
                                    <td className="px-4 py-3 text-right font-medium">
                                        {item.line_total_formatted}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                <div className="ml-auto w-full max-w-sm space-y-2 rounded-xl border border-border/80 bg-card p-4 text-sm">
                    <Row label="Subtotal" value={slip.subtotal_formatted} />
                    {slip.adjustment !== 0 && (
                        <Row
                            label="Adjustment"
                            value={slip.adjustment_formatted}
                        />
                    )}
                    <Row
                        label="Total"
                        value={slip.total_formatted}
                        emphasize
                    />
                </div>

                {slip.notes && (
                    <div className="rounded-xl border border-border/80 bg-card p-4">
                        <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                            Notes
                        </p>
                        <p className="mt-2 text-sm whitespace-pre-wrap">
                            {slip.notes}
                        </p>
                    </div>
                )}
            </div>
        </>
    );
}

function Detail({ label, value }: { label: string; value: string }) {
    return (
        <div className="rounded-xl border border-border/80 bg-card p-4">
            <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                {label}
            </p>
            <p className="mt-1 font-medium">{value}</p>
        </div>
    );
}

function Row({
    label,
    value,
    emphasize,
}: {
    label: string;
    value: string;
    emphasize?: boolean;
}) {
    return (
        <div className="flex justify-between gap-4">
            <span className="text-muted-foreground">{label}</span>
            <span
                className={
                    emphasize
                        ? 'font-display text-lg font-semibold'
                        : 'font-medium'
                }
            >
                {value}
            </span>
        </div>
    );
}

BillSlipsShow.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Bill slips', href: '/admin/bill-slips' },
        { title: 'Slip', href: '#' },
    ],
};
