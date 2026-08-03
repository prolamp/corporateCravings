import { Head, Link, router } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { Pagination } from '@/components/pagination';
import { StatusBadge } from '@/components/status-badge/status-badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

export type BillSlipRow = {
    id: number;
    slip_number: string;
    company_name: string | null;
    period_label: string;
    total_formatted: string;
    items_count: number;
    status: string;
    status_label: string;
    status_badge: string;
    created_at_formatted: string | null;
    can_mark_paid: boolean;
};

type Paginator = {
    data: BillSlipRow[];
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number | null;
        to: number | null;
    };
};

type StatusOption = { value: string; label: string };
type SelectOption = { value: number; label: string };

type Props = {
    slips: Paginator;
    filters: {
        search: string | null;
        status: string | null;
        user_id: number | null;
        contract_id: number | null;
        date_from: string | null;
        date_to: string | null;
    };
    statusOptions: StatusOption[];
    contractOptions: SelectOption[];
    canManage: boolean;
};

export default function BillSlipsIndex({
    slips,
    filters,
    statusOptions,
    contractOptions,
    canManage,
}: Props) {
    function applyFilters(next: Partial<Props['filters']>) {
        router.get(
            '/admin/bill-slips',
            {
                search: next.search ?? filters.search ?? '',
                status: next.status ?? filters.status ?? '',
                contract_id: next.contract_id ?? filters.contract_id ?? '',
                date_from: next.date_from ?? filters.date_from ?? '',
                date_to: next.date_to ?? filters.date_to ?? '',
            },
            { preserveState: true, replace: true },
        );
    }

    function pageUrl(page: number): string {
        const params = new URLSearchParams();
        if (filters.search) params.set('search', filters.search);
        if (filters.status) params.set('status', filters.status);
        if (filters.contract_id)
            params.set('contract_id', String(filters.contract_id));
        if (filters.date_from) params.set('date_from', filters.date_from);
        if (filters.date_to) params.set('date_to', filters.date_to);
        if (page > 1) params.set('page', String(page));
        const query = params.toString();

        return query ? `/admin/bill-slips?${query}` : '/admin/bill-slips';
    }

    function markPaid(id: number) {
        router.patch(`/admin/bill-slips/${id}/mark-paid`, {}, { preserveScroll: true });
    }

    return (
        <>
            <Head title="Bill slips" />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                            Packages
                        </p>
                        <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight">
                            Bill slips
                        </h2>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Generate receipts from delivered subscription days
                        </p>
                    </div>
                    {canManage && (
                        <Button asChild>
                            <Link href="/admin/bill-slips/create">
                                <Plus className="size-4" />
                                Create bill slip
                            </Link>
                        </Button>
                    )}
                </div>

                <div className="flex flex-col gap-3 rounded-xl border border-border/80 bg-card p-4 md:flex-row md:flex-wrap md:items-end">
                    <div className="min-w-[12rem] flex-1">
                        <label className="text-xs font-medium text-muted-foreground">
                            Search
                        </label>
                        <Input
                            className="mt-1"
                            defaultValue={filters.search ?? ''}
                            placeholder="Slip # or company"
                            onBlur={(e) =>
                                applyFilters({ search: e.target.value || null })
                            }
                            onKeyDown={(e) => {
                                if (e.key === 'Enter') {
                                    applyFilters({
                                        search:
                                            (e.target as HTMLInputElement)
                                                .value || null,
                                    });
                                }
                            }}
                        />
                    </div>
                    <div className="w-full md:w-40">
                        <label className="text-xs font-medium text-muted-foreground">
                            Status
                        </label>
                        <select
                            className="mt-1 flex h-9 w-full rounded-md border border-input bg-background px-3 text-sm"
                            value={filters.status ?? ''}
                            onChange={(e) =>
                                applyFilters({
                                    status: e.target.value || null,
                                })
                            }
                        >
                            <option value="">All</option>
                            {statusOptions.map((opt) => (
                                <option key={opt.value} value={opt.value}>
                                    {opt.label}
                                </option>
                            ))}
                        </select>
                    </div>
                    <div className="min-w-[14rem] flex-1">
                        <label className="text-xs font-medium text-muted-foreground">
                            Contract
                        </label>
                        <select
                            className="mt-1 flex h-9 w-full rounded-md border border-input bg-background px-3 text-sm"
                            value={filters.contract_id ?? ''}
                            onChange={(e) =>
                                applyFilters({
                                    contract_id: e.target.value
                                        ? Number(e.target.value)
                                        : null,
                                })
                            }
                        >
                            <option value="">All contracts</option>
                            {contractOptions.map((opt) => (
                                <option key={opt.value} value={opt.value}>
                                    {opt.label}
                                </option>
                            ))}
                        </select>
                    </div>
                    <div className="w-full md:w-40">
                        <label className="text-xs font-medium text-muted-foreground">
                            From
                        </label>
                        <Input
                            type="date"
                            className="mt-1"
                            value={filters.date_from ?? ''}
                            onChange={(e) =>
                                applyFilters({
                                    date_from: e.target.value || null,
                                })
                            }
                        />
                    </div>
                    <div className="w-full md:w-40">
                        <label className="text-xs font-medium text-muted-foreground">
                            To
                        </label>
                        <Input
                            type="date"
                            className="mt-1"
                            value={filters.date_to ?? ''}
                            onChange={(e) =>
                                applyFilters({
                                    date_to: e.target.value || null,
                                })
                            }
                        />
                    </div>
                </div>

                <div className="overflow-x-auto rounded-xl border border-border/80 bg-card">
                    <table className="w-full text-left text-sm">
                        <thead className="border-b text-[11px] font-bold tracking-[0.08em] text-muted-foreground uppercase">
                            <tr>
                                <th className="px-4 py-3">Slip</th>
                                <th className="px-4 py-3">Company</th>
                                <th className="px-4 py-3">Period</th>
                                <th className="px-4 py-3">Days</th>
                                <th className="px-4 py-3">Total</th>
                                <th className="px-4 py-3">Status</th>
                                <th className="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {slips.data.length === 0 ? (
                                <tr>
                                    <td
                                        colSpan={7}
                                        className="px-4 py-10 text-center text-muted-foreground"
                                    >
                                        No bill slips yet.
                                    </td>
                                </tr>
                            ) : (
                                slips.data.map((slip) => (
                                    <tr
                                        key={slip.id}
                                        className="border-b last:border-0"
                                    >
                                        <td className="px-4 py-3 font-medium">
                                            <Link
                                                href={`/admin/bill-slips/${slip.id}`}
                                                className="hover:underline"
                                            >
                                                {slip.slip_number}
                                            </Link>
                                        </td>
                                        <td className="px-4 py-3">
                                            {slip.company_name}
                                        </td>
                                        <td className="px-4 py-3 text-muted-foreground">
                                            {slip.period_label}
                                        </td>
                                        <td className="px-4 py-3">
                                            {slip.items_count}
                                        </td>
                                        <td className="px-4 py-3 font-medium">
                                            {slip.total_formatted}
                                        </td>
                                        <td className="px-4 py-3">
                                            <StatusBadge
                                                label={slip.status_label}
                                                badge={slip.status_badge}
                                            />
                                        </td>
                                        <td className="px-4 py-3 text-right">
                                            <div className="flex justify-end gap-2">
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    asChild
                                                >
                                                    <Link
                                                        href={`/admin/bill-slips/${slip.id}`}
                                                    >
                                                        Open
                                                    </Link>
                                                </Button>
                                                {canManage &&
                                                    slip.can_mark_paid && (
                                                        <Button
                                                            size="sm"
                                                            onClick={() =>
                                                                markPaid(
                                                                    slip.id,
                                                                )
                                                            }
                                                        >
                                                            Mark paid
                                                        </Button>
                                                    )}
                                            </div>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>

                <Pagination
                    meta={{
                        current_page: slips.meta.current_page,
                        last_page: slips.meta.last_page,
                        per_page: slips.meta.per_page,
                        total: slips.meta.total,
                        from: slips.meta.from,
                        to: slips.meta.to,
                    }}
                    pageUrl={pageUrl}
                />
            </div>
        </>
    );
}

BillSlipsIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Bill slips', href: '/admin/bill-slips' },
    ],
};
