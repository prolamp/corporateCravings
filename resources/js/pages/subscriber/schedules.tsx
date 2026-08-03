import { Head, Link, router } from '@inertiajs/react';
import {
    ScheduleRow,
    type ScheduleEntry,
} from '@/components/subscriber/schedule-row';
import { Button } from '@/components/ui/button';

type Props = {
    month: string;
    month_label: string;
    prev_month: string;
    next_month: string;
    contract: { id: number; package_title: string | null } | null;
    entries: ScheduleEntry[];
    has_contracts: boolean;
};

export default function SubscriberSchedules({
    month_label,
    prev_month,
    next_month,
    contract,
    entries,
    has_contracts,
}: Props) {
    function go(month: string) {
        router.get(
            '/subscriber/schedules',
            { month },
            { preserveState: true, replace: true },
        );
    }

    return (
        <>
            <Head title="Schedules" />

            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h1 className="font-display text-2xl font-semibold tracking-tight md:text-3xl">
                            Schedules
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Your meal calendar
                            {contract?.package_title
                                ? ` for ${contract.package_title}`
                                : ''}
                            .
                        </p>
                    </div>
                    <div className="flex gap-2">
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            onClick={() => go(prev_month)}
                        >
                            Previous
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            onClick={() => go(next_month)}
                        >
                            Next
                        </Button>
                    </div>
                </div>

                <h2 className="font-display text-xl font-semibold tracking-tight">
                    {month_label}
                </h2>

                {!has_contracts ? (
                    <div className="rounded-xl border border-dashed border-border/80 px-6 py-12 text-center text-sm text-muted-foreground">
                        No contract to show schedules for.
                    </div>
                ) : entries.length === 0 ? (
                    <div className="rounded-xl border border-dashed border-border/80 px-6 py-12 text-center text-sm text-muted-foreground">
                        No meals scheduled this month.
                    </div>
                ) : (
                    <ul className="space-y-3">
                        {entries.map((entry) => (
                            <ScheduleRow key={entry.id} entry={entry} />
                        ))}
                    </ul>
                )}

                <p className="text-sm text-muted-foreground">
                    Need a change?{' '}
                    <Link
                        href="/contact"
                        className="font-medium text-primary underline-offset-4 hover:underline"
                    >
                        Contact us
                    </Link>
                    .
                </p>
            </div>
        </>
    );
}

SubscriberSchedules.layout = {
    breadcrumbs: [
        { title: 'Subscriber', href: '/subscriber/dashboard' },
        { title: 'Schedules', href: '/subscriber/schedules' },
    ],
};
