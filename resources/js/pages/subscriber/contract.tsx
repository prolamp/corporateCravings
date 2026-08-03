import { Head } from '@inertiajs/react';
import { StatusBadge } from '@/components/status-badge/status-badge';

type ContractDetail = {
    id: number;
    package_title: string | null;
    meal_type_label: string;
    persons: number;
    agreed_total_formatted: string;
    has_discount: boolean;
    package_amount_formatted: string;
    discount_formatted: string;
    day_bill_formatted: string;
    days_of_week_labels: string[];
    delivery_time: string | null;
    starts_on_formatted: string | null;
    ends_on_formatted: string | null;
    status_label: string;
    status_badge: string;
    notes: string | null;
};

type Props = {
    contract: ContractDetail | null;
};

export default function SubscriberContract({ contract }: Props) {
    return (
        <>
            <Head title="Contract" />

            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div>
                    <h1 className="font-display text-2xl font-semibold tracking-tight md:text-3xl">
                        Your contract
                    </h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Private pricing visible only to you and Corporate
                        Craving.
                    </p>
                </div>

                {!contract ? (
                    <div className="rounded-xl border border-dashed border-border/80 px-6 py-12 text-center text-sm text-muted-foreground">
                        No contract on file yet.
                    </div>
                ) : (
                    <div className="overflow-hidden rounded-xl border border-border/80 bg-card">
                        <div className="flex flex-wrap items-start justify-between gap-3 border-b border-border/80 px-5 py-5 md:px-6">
                            <div>
                                <p className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                                    Package
                                </p>
                                <h2 className="mt-1 font-display text-xl font-semibold tracking-tight">
                                    {contract.package_title ?? 'Contract package'}
                                </h2>
                                <p className="mt-2 text-sm text-muted-foreground">
                                    {contract.meal_type_label} ·{' '}
                                    {contract.persons} persons
                                    {contract.delivery_time
                                        ? ` · ${contract.delivery_time}`
                                        : ''}
                                </p>
                            </div>
                            <StatusBadge
                                label={contract.status_label}
                                badge={contract.status_badge}
                            />
                        </div>

                        <dl className="grid gap-5 px-5 py-5 sm:grid-cols-2 md:px-6">
                            {contract.has_discount && (
                                <>
                                    <Row
                                        label="Package amount"
                                        value={
                                            contract.package_amount_formatted
                                        }
                                    />
                                    <Row
                                        label="Discount"
                                        value={contract.discount_formatted}
                                    />
                                </>
                            )}
                            <Row
                                label="Agreed total / person"
                                value={contract.agreed_total_formatted}
                                emphasize
                            />
                            <Row
                                label="Day bill"
                                value={contract.day_bill_formatted}
                            />
                            <Row
                                label="Meal days"
                                value={
                                    contract.days_of_week_labels.length > 0
                                        ? contract.days_of_week_labels.join(', ')
                                        : '—'
                                }
                            />
                            <Row
                                label="Starts"
                                value={contract.starts_on_formatted ?? '—'}
                            />
                            <Row
                                label="Ends"
                                value={contract.ends_on_formatted ?? 'Open'}
                            />
                        </dl>

                        {contract.notes && (
                            <div className="border-t border-border/80 px-5 py-4 md:px-6">
                                <p className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                                    Notes
                                </p>
                                <p className="mt-2 text-sm leading-relaxed">
                                    {contract.notes}
                                </p>
                            </div>
                        )}
                    </div>
                )}
            </div>
        </>
    );
}

function Row({
    label,
    value,
    emphasize = false,
}: {
    label: string;
    value: string;
    emphasize?: boolean;
}) {
    return (
        <div>
            <dt className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                {label}
            </dt>
            <dd
                className={
                    emphasize
                        ? 'mt-1 font-display text-lg font-bold text-primary'
                        : 'mt-1 font-medium'
                }
            >
                {value}
            </dd>
        </div>
    );
}

SubscriberContract.layout = {
    breadcrumbs: [
        { title: 'Subscriber', href: '/subscriber/dashboard' },
        { title: 'Contract', href: '/subscriber/contract' },
    ],
};
