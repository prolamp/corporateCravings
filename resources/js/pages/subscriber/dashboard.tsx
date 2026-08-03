import { Head, Link } from '@inertiajs/react';
import {
    ScheduleRow,
    type ScheduleEntry,
} from '@/components/subscriber/schedule-row';
import { StatusBadge } from '@/components/status-badge/status-badge';
import { Button } from '@/components/ui/button';

type ContractSummary = {
    id: number;
    package_title: string | null;
    meal_type: string;
    meal_type_label: string;
    persons: number;
    agreed_total_formatted: string;
    status: string;
    status_label: string;
    status_badge: string;
};

type Props = {
    stats: {
        delivered_orders: number;
        total_bill: number;
        total_bill_formatted: string;
        persons_served: number;
    };
    contract: ContractSummary | null;
    upcoming: ScheduleEntry[];
    links: {
        schedules: boolean;
        history: boolean;
        contract: boolean;
    };
};

export default function SubscriberDashboard({
    stats,
    contract,
    upcoming,
}: Props) {
    return (
        <>
            <Head title="Subscriber Dashboard" />

            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div>
                    <h1 className="font-display text-2xl font-semibold tracking-tight md:text-3xl">
                        Your dashboard
                    </h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Deliveries, billing, and your private contract rate.
                    </p>
                </div>

                <div className="grid gap-4 sm:grid-cols-3">
                    <Stat
                        label="Orders delivered"
                        value={stats.delivered_orders.toLocaleString()}
                    />
                    <Stat label="Total bill" value={stats.total_bill_formatted} />
                    <Stat
                        label="Persons served"
                        value={stats.persons_served.toLocaleString()}
                    />
                </div>

                {contract ? (
                    <section className="rounded-xl border border-border/80 bg-card p-5">
                        <div className="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <h2 className="font-medium">Active contract</h2>
                                <p className="mt-2 text-sm text-muted-foreground">
                                    {contract.package_title ?? 'Package'} ·{' '}
                                    {contract.meal_type_label} ·{' '}
                                    {contract.persons} persons
                                </p>
                                <p className="mt-2 font-semibold">
                                    Agreed rate {contract.agreed_total_formatted}{' '}
                                    / person
                                </p>
                            </div>
                            <StatusBadge
                                label={contract.status_label}
                                badge={contract.status_badge}
                            />
                        </div>
                        <Button asChild variant="outline" size="sm" className="mt-4">
                            <Link href="/subscriber/contract">View contract</Link>
                        </Button>
                    </section>
                ) : (
                    <div className="rounded-xl border border-dashed border-border/80 px-6 py-10 text-center text-sm text-muted-foreground">
                        No active contract yet. Contact Corporate Cravings if you
                        need help.
                    </div>
                )}

                <section>
                    <div className="mb-3 flex items-end justify-between gap-3">
                        <h2 className="font-display text-xl font-semibold tracking-tight">
                            Upcoming meals
                        </h2>
                        <Link
                            href="/subscriber/schedules"
                            className="text-sm font-medium text-primary underline-offset-4 hover:underline"
                        >
                            Full schedule
                        </Link>
                    </div>

                    {upcoming.length === 0 ? (
                        <div className="rounded-xl border border-dashed border-border/80 px-6 py-10 text-center text-sm text-muted-foreground">
                            No upcoming scheduled meals.
                        </div>
                    ) : (
                        <ul className="space-y-3">
                            {upcoming.map((entry) => (
                                <ScheduleRow key={entry.id} entry={entry} />
                            ))}
                        </ul>
                    )}
                </section>
            </div>
        </>
    );
}

function Stat({ label, value }: { label: string; value: string }) {
    return (
        <div className="rounded-xl border border-border/80 bg-card p-5">
            <p className="text-sm text-muted-foreground">{label}</p>
            <p className="mt-2 font-display text-2xl font-semibold tabular-nums">
                {value}
            </p>
        </div>
    );
}

SubscriberDashboard.layout = {
    breadcrumbs: [
        { title: 'Subscriber', href: '/subscriber/dashboard' },
        { title: 'Dashboard', href: '/subscriber/dashboard' },
    ],
};
