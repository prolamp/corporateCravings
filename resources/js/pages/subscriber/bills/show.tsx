import { Head, Link } from '@inertiajs/react';
import { StatusBadge } from '@/components/status-badge/status-badge';
import { Button } from '@/components/ui/button';

type SlipItem = {
    id: number;
    date_formatted: string | null;
    dish_title: string | null;
    persons: number;
    line_total_formatted: string;
};

type Slip = {
    id: number;
    slip_number: string;
    period_label: string;
    subtotal_formatted: string;
    adjustment: number;
    adjustment_formatted: string;
    total_formatted: string;
    status_label: string;
    status_badge: string;
    paid_at_formatted: string | null;
    notes: string | null;
    items: SlipItem[];
};

type Props = { slip: Slip };

export default function SubscriberBillShow({ slip }: Props) {
    return (
        <>
            <Head title={slip.slip_number} />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h1 className="font-display text-2xl font-semibold tracking-tight">
                            {slip.slip_number}
                        </h1>
                        <div className="mt-2 flex flex-wrap items-center gap-2">
                            <StatusBadge
                                label={slip.status_label}
                                badge={slip.status_badge}
                            />
                            <span className="text-sm text-muted-foreground">
                                {slip.period_label}
                            </span>
                        </div>
                    </div>
                    <Button variant="outline" asChild>
                        <Link
                            href={`/subscriber/bills/${slip.id}/receipt`}
                            target="_blank"
                        >
                            Download receipt
                        </Link>
                    </Button>
                </div>

                <div className="overflow-x-auto rounded-xl border border-border/80 bg-card">
                    <table className="w-full text-left text-sm">
                        <thead className="border-b text-[11px] font-bold tracking-[0.08em] text-muted-foreground uppercase">
                            <tr>
                                <th className="px-4 py-3">Date</th>
                                <th className="px-4 py-3">Dish</th>
                                <th className="px-4 py-3">Persons</th>
                                <th className="px-4 py-3 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            {slip.items.map((item) => (
                                <tr
                                    key={item.id}
                                    className="border-b last:border-0"
                                >
                                    <td className="px-4 py-3">{item.date_formatted}</td>
                                    <td className="px-4 py-3">{item.dish_title}</td>
                                    <td className="px-4 py-3">{item.persons}</td>
                                    <td className="px-4 py-3 text-right font-medium">
                                        {item.line_total_formatted}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                <div className="ml-auto w-full max-w-sm space-y-2 rounded-xl border border-border/80 bg-card p-4 text-sm">
                    <div className="flex justify-between">
                        <span className="text-muted-foreground">Subtotal</span>
                        <span>{slip.subtotal_formatted}</span>
                    </div>
                    {slip.adjustment !== 0 && (
                        <div className="flex justify-between">
                            <span className="text-muted-foreground">
                                Adjustment
                            </span>
                            <span>{slip.adjustment_formatted}</span>
                        </div>
                    )}
                    <div className="flex justify-between border-t pt-2 font-display text-lg font-semibold">
                        <span>Total</span>
                        <span>{slip.total_formatted}</span>
                    </div>
                </div>

                {slip.notes && (
                    <p className="text-sm text-muted-foreground">
                        Notes: {slip.notes}
                    </p>
                )}
            </div>
        </>
    );
}

SubscriberBillShow.layout = {
    breadcrumbs: [
        { title: 'Subscriber', href: '/subscriber/dashboard' },
        { title: 'Bills', href: '/subscriber/bills' },
        { title: 'Slip', href: '#' },
    ],
};
