import { Head, Link } from '@inertiajs/react';
import { StatusBadge } from '@/components/status-badge/status-badge';
import { StatusToggle } from '@/components/status-toggle';
import { Button } from '@/components/ui/button';
import {
    destroyContract,
    type AdminContract,
} from '@/pages/admin/contracts/contract-form';

type Props = {
    contract: AdminContract;
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
};

const dayLabels: Record<string, string> = {
    mon: 'Mon',
    tue: 'Tue',
    wed: 'Wed',
    thu: 'Thu',
    fri: 'Fri',
    sat: 'Sat',
    sun: 'Sun',
};

export default function ContractsShow({
    contract,
    recentSlips,
    canManage,
    canManageBillSlips,
}: Props) {
    const title =
        contract.company_name || contract.subscriber_name || 'Contract';

    return (
        <>
            <Head title={title} />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                            Packages
                        </p>
                        <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight">
                            {title}
                        </h2>
                        <div className="mt-2 flex flex-wrap items-center gap-2">
                            <StatusBadge
                                label={contract.status_label}
                                badge={contract.status_badge}
                            />
                            <span className="text-sm text-muted-foreground">
                                {contract.package_title || 'No package'}
                                {contract.subscriber_name
                                    ? ` · ${contract.subscriber_name}`
                                    : ''}
                            </span>
                        </div>
                    </div>
                    <div className="flex flex-wrap items-center gap-2">
                        <StatusToggle
                            endpoint={`/admin/contracts/${contract.id}/status`}
                            status={
                                contract.status === 'active'
                                    ? 'active'
                                    : 'inactive'
                            }
                            canToggle={canManage}
                            activeLabel="Deactivate contract"
                            inactiveLabel="Activate contract"
                        />
                        <Button variant="outline" asChild>
                            <Link href="/admin/contracts">Back</Link>
                        </Button>
                        <Button variant="outline" asChild>
                            <Link
                                href={`/admin/subscribers/${contract.user_id}`}
                            >
                                Subscriber
                            </Link>
                        </Button>
                        {canManage && (
                            <Button asChild>
                                <Link
                                    href={`/admin/contracts/${contract.id}/edit`}
                                >
                                    Edit
                                </Link>
                            </Button>
                        )}
                        {canManage && contract.can_delete && (
                            <Button
                                variant="destructive"
                                type="button"
                                onClick={() => destroyContract(contract.id)}
                            >
                                Delete
                            </Button>
                        )}
                    </div>
                </div>

                <div className="grid gap-4 rounded-xl border border-border/80 bg-card p-4 md:grid-cols-3 md:p-6">
                    <Detail
                        label="Meal"
                        value={
                            contract.meal_type
                                ? contract.meal_type.charAt(0).toUpperCase() +
                                  contract.meal_type.slice(1)
                                : '—'
                        }
                    />
                    <Detail
                        label="Persons / day"
                        value={String(contract.persons)}
                    />
                    <Detail
                        label="Delivery time"
                        value={contract.delivery_time || '—'}
                    />
                    <Detail
                        label="Days"
                        value={
                            contract.days_of_week
                                .map((d) => dayLabels[d] ?? d)
                                .join(', ') || '—'
                        }
                    />
                    <Detail
                        label="Starts"
                        value={contract.starts_on || '—'}
                    />
                    <Detail label="Ends" value={contract.ends_on || '—'} />
                </div>

                <div className="rounded-xl border border-border/80 bg-card p-4 md:p-6">
                    <h3 className="font-display text-lg font-semibold tracking-tight">
                        Private pricing
                    </h3>
                    <p className="text-sm text-muted-foreground">
                        Package rate comes from the package; you only set the
                        discount. Visible to staff and this subscriber only.
                    </p>

                    <div className="mt-4 grid gap-4 md:grid-cols-3">
                        {contract.discount > 0 && (
                            <>
                                <Detail
                                    label="Package rate /pp"
                                    hint="From selected package"
                                    value={
                                        contract.package_public_total_formatted ||
                                        contract.package_amount_formatted
                                    }
                                />
                                <Detail
                                    label="Discount /pp"
                                    value={contract.discount_formatted}
                                />
                            </>
                        )}
                        <Detail
                            label="Contract rate /pp"
                            hint={
                                contract.discount > 0
                                    ? 'Package rate − discount'
                                    : undefined
                            }
                            value={contract.agreed_total_formatted}
                            emphasize
                        />
                    </div>

                    <div className="mt-6 grid gap-4 border-t border-border/60 pt-4 md:grid-cols-2">
                        <Detail
                            label="Est. day bill"
                            hint={`${contract.agreed_total_formatted} × ${contract.persons} persons`}
                            value={
                                contract.day_bill_formatted ||
                                '—'
                            }
                            emphasize
                        />
                        <Detail
                            label="Est. month bill"
                            hint={`${contract.meal_days_per_week ?? contract.days_of_week.length} meal days/week × ~4.3 weeks (approx.)`}
                            value={
                                contract.month_bill_estimate_formatted ||
                                '—'
                            }
                            emphasize
                        />
                    </div>

                    {contract.notes && (
                        <div className="mt-4">
                            <Detail label="Notes" value={contract.notes} />
                        </div>
                    )}
                </div>

                <div className="rounded-xl border border-border/80 bg-card p-4 md:p-6">
                    <div className="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <h3 className="font-display text-lg font-semibold tracking-tight">
                                Bill slips / receipts
                            </h3>
                            <p className="mt-1 text-sm text-muted-foreground">
                                Receipts generated from delivered schedule days
                            </p>
                        </div>
                        {canManageBillSlips && (
                            <Button asChild>
                                <Link
                                    href={`/admin/bill-slips/create?contract_id=${contract.id}`}
                                >
                                    Create bill slip
                                </Link>
                            </Button>
                        )}
                    </div>
                    {recentSlips.length === 0 ? (
                        <p className="mt-4 text-sm text-muted-foreground">
                            No bill slips for this contract yet.
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
                </div>

                {contract.history.length > 0 && (
                    <div className="rounded-xl border border-border/80 bg-card p-4 md:p-6">
                        <h3 className="font-display text-lg font-semibold tracking-tight">
                            Pricing history
                        </h3>
                        <div className="mt-4 overflow-x-auto">
                            <table className="w-full text-left text-sm">
                                <thead className="border-b text-[11px] font-bold tracking-[0.08em] text-muted-foreground uppercase">
                                    <tr>
                                        <th className="pb-2 pr-4">Package</th>
                                        <th className="pb-2 pr-4">Amount</th>
                                        <th className="pb-2 pr-4">Discount</th>
                                        <th className="pb-2 pr-4">Agreed</th>
                                        <th className="pb-2 pr-4">Persons</th>
                                        <th className="pb-2">From</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {contract.history.map((row) => (
                                        <tr
                                            key={row.id}
                                            className="border-b last:border-0"
                                        >
                                            <td className="py-2 pr-4">
                                                {row.package_title || '—'}
                                            </td>
                                            <td className="py-2 pr-4">
                                                {
                                                    row.package_amount_formatted
                                                }
                                            </td>
                                            <td className="py-2 pr-4">
                                                {row.discount_formatted}
                                            </td>
                                            <td className="py-2 pr-4 font-medium">
                                                {row.agreed_total_formatted}
                                            </td>
                                            <td className="py-2 pr-4">
                                                {row.persons}
                                            </td>
                                            <td className="py-2 text-muted-foreground">
                                                {row.effective_from
                                                    ? new Date(
                                                          row.effective_from,
                                                      ).toLocaleDateString()
                                                    : '—'}
                                                {row.effective_to
                                                    ? ` → ${new Date(row.effective_to).toLocaleDateString()}`
                                                    : ' → current'}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                )}
            </div>
        </>
    );
}

function Detail({
    label,
    value,
    hint,
    emphasize = false,
}: {
    label: string;
    value: string;
    hint?: string;
    emphasize?: boolean;
}) {
    return (
        <div>
            <p className="text-[11px] font-bold tracking-[0.08em] text-muted-foreground uppercase">
                {label}
            </p>
            <p
                className={
                    emphasize
                        ? 'mt-1 font-display text-lg font-semibold tracking-tight'
                        : 'mt-1 text-sm font-medium whitespace-pre-wrap'
                }
            >
                {value}
            </p>
            {hint && (
                <p className="mt-1 text-xs text-muted-foreground">{hint}</p>
            )}
        </div>
    );
}

ContractsShow.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Contracts', href: '/admin/contracts' },
        { title: 'Details', href: '#' },
    ],
};
