import { Head } from '@inertiajs/react';
import {
    ScheduleRow,
    type ScheduleEntry,
} from '@/components/subscriber/schedule-row';
import { Pagination, type PaginationMeta } from '@/components/pagination';

type Props = {
    entries: {
        data: ScheduleEntry[];
        meta: PaginationMeta;
    };
};

export default function SubscriberHistory({ entries }: Props) {
    function pageUrl(page: number): string {
        return page > 1
            ? `/subscriber/history?page=${page}`
            : '/subscriber/history';
    }

    return (
        <>
            <Head title="Delivery history" />

            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div>
                    <h1 className="font-display text-2xl font-semibold tracking-tight md:text-3xl">
                        Delivery history
                    </h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Past delivered meals and billed amounts.
                    </p>
                </div>

                {entries.data.length === 0 ? (
                    <div className="rounded-xl border border-dashed border-border/80 px-6 py-12 text-center text-sm text-muted-foreground">
                        No delivered meals yet.
                    </div>
                ) : (
                    <>
                        <ul className="space-y-3">
                            {entries.data.map((entry) => (
                                <ScheduleRow key={entry.id} entry={entry} />
                            ))}
                        </ul>
                        <Pagination meta={entries.meta} pageUrl={pageUrl} />
                    </>
                )}
            </div>
        </>
    );
}

SubscriberHistory.layout = {
    breadcrumbs: [
        { title: 'Subscriber', href: '/subscriber/dashboard' },
        { title: 'History', href: '/subscriber/history' },
    ],
};
