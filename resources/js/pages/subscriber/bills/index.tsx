import { Head, Link, router } from '@inertiajs/react';
import { Pagination, type PaginationMeta } from '@/components/pagination';
import { StatusBadge } from '@/components/status-badge/status-badge';

type Slip = {
    id: number;
    slip_number: string;
    period_label: string;
    total_formatted: string;
    items_count: number;
    status_label: string;
    status_badge: string;
    created_at_formatted: string | null;
};

type Props = {
    slips: {
        data: Slip[];
        meta: PaginationMeta;
    };
    filters: { status: string | null };
};

export default function SubscriberBillsIndex({ slips, filters }: Props) {
    function pageUrl(page: number): string {
        const params = new URLSearchParams();
        if (filters.status) params.set('status', filters.status);
        if (page > 1) params.set('page', String(page));
        const q = params.toString();

        return q ? `/subscriber/bills?${q}` : '/subscriber/bills';
    }

    return (
        <>
            <Head title="Bills" />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h1 className="font-display text-2xl font-semibold tracking-tight md:text-3xl">
                            Bills
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            View and download your bill slips
                        </p>
                    </div>
                    <select
                        className="flex h-9 rounded-md border border-input bg-background px-3 text-sm"
                        value={filters.status ?? ''}
                        onChange={(e) =>
                            router.get(
                                '/subscriber/bills',
                                { status: e.target.value || undefined },
                                { preserveState: true, replace: true },
                            )
                        }
                    >
                        <option value="">All</option>
                        <option value="issued">Issued</option>
                        <option value="paid">Paid</option>
                    </select>
                </div>

                {slips.data.length === 0 ? (
                    <div className="rounded-xl border border-dashed border-border/80 px-6 py-12 text-center text-sm text-muted-foreground">
                        No bill slips yet.
                    </div>
                ) : (
                    <>
                        <ul className="space-y-3">
                            {slips.data.map((slip) => (
                                <li key={slip.id}>
                                    <Link
                                        href={`/subscriber/bills/${slip.id}`}
                                        className="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-border/80 bg-card p-4 transition-colors hover:border-foreground/20 md:p-5"
                                    >
                                        <div>
                                            <p className="font-semibold">
                                                {slip.slip_number}
                                            </p>
                                            <p className="mt-1 text-sm text-muted-foreground">
                                                {slip.period_label} ·{' '}
                                                {slip.items_count} days ·{' '}
                                                {slip.created_at_formatted}
                                            </p>
                                        </div>
                                        <div className="flex items-center gap-3">
                                            <StatusBadge
                                                label={slip.status_label}
                                                badge={slip.status_badge}
                                            />
                                            <span className="font-display text-lg font-bold tabular-nums">
                                                {slip.total_formatted}
                                            </span>
                                        </div>
                                    </Link>
                                </li>
                            ))}
                        </ul>
                        <Pagination meta={slips.meta} pageUrl={pageUrl} />
                    </>
                )}
            </div>
        </>
    );
}

SubscriberBillsIndex.layout = {
    breadcrumbs: [
        { title: 'Subscriber', href: '/subscriber/dashboard' },
        { title: 'Bills', href: '/subscriber/bills' },
    ],
};
