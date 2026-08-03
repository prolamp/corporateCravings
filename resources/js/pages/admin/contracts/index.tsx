import { Head, Link, router } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { Pagination } from '@/components/pagination';
import { StatusBadge } from '@/components/status-badge/status-badge';
import { StatusToggle } from '@/components/status-toggle';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type {
    AdminContract,
    StatusOption,
} from '@/pages/admin/contracts/contract-form';

type Paginator = {
    data: AdminContract[];
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number | null;
        to: number | null;
    };
};

type Props = {
    contracts: Paginator;
    filters: {
        search: string | null;
        status: string | null;
        user_id: number | null;
    };
    statusOptions: StatusOption[];
    canManage: boolean;
};

export default function ContractsIndex({
    contracts,
    filters,
    statusOptions,
    canManage,
}: Props) {
    function applyFilters(next: Partial<Props['filters']>) {
        router.get(
            '/admin/contracts',
            {
                search: next.search ?? filters.search ?? '',
                status: next.status ?? filters.status ?? '',
                user_id: next.user_id ?? filters.user_id ?? '',
            },
            { preserveState: true, replace: true },
        );
    }

    function pageUrl(page: number): string {
        const params = new URLSearchParams();
        if (filters.search) {
            params.set('search', filters.search);
        }
        if (filters.status) {
            params.set('status', filters.status);
        }
        if (filters.user_id) {
            params.set('user_id', String(filters.user_id));
        }
        if (page > 1) {
            params.set('page', String(page));
        }
        const query = params.toString();

        return query ? `/admin/contracts?${query}` : '/admin/contracts';
    }

    return (
        <>
            <Head title="Contracts" />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                            Packages
                        </p>
                        <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight md:text-3xl">
                            Subscriber contracts
                        </h2>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Private package amount, discount, and agreed total
                            per person.
                        </p>
                    </div>
                    {canManage && (
                        <Button asChild>
                            <Link href="/admin/contracts/create">
                                <Plus className="size-4" />
                                Add contract
                            </Link>
                        </Button>
                    )}
                </div>

                <div className="grid gap-3 rounded-xl border border-border/80 bg-card p-4 md:grid-cols-3">
                    <Input
                        key={`search-${filters.search ?? ''}-${filters.status ?? ''}`}
                        placeholder="Search company, contact, package…"
                        defaultValue={filters.search ?? ''}
                        onKeyDown={(e) => {
                            if (e.key === 'Enter') {
                                applyFilters({
                                    search: (
                                        e.target as HTMLInputElement
                                    ).value,
                                });
                            }
                        }}
                    />
                    <select
                        className="border-input h-9 rounded-md border bg-background px-3 text-sm"
                        value={filters.status ?? ''}
                        onChange={(e) =>
                            applyFilters({ status: e.target.value || null })
                        }
                    >
                        <option value="">All statuses</option>
                        {statusOptions.map((option) => (
                            <option key={option.value} value={option.value}>
                                {option.label}
                            </option>
                        ))}
                    </select>
                    <Button
                        type="button"
                        variant="outline"
                        onClick={() =>
                            router.get(
                                '/admin/contracts',
                                {},
                                { replace: true },
                            )
                        }
                    >
                        Clear filters
                    </Button>
                </div>

                <div className="overflow-hidden rounded-xl border border-border/80">
                    <table className="w-full text-left text-sm">
                        <thead className="border-b bg-muted/40 text-[11px] font-bold tracking-[0.08em] text-muted-foreground uppercase">
                            <tr>
                                <th className="px-4 py-3">Company</th>
                                <th className="px-4 py-3">Package</th>
                                <th className="px-4 py-3">Persons</th>
                                <th className="px-4 py-3">Agreed /pp</th>
                                <th className="px-4 py-3">Status</th>
                                <th className="px-4 py-3 text-right">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {contracts.data.length === 0 ? (
                                <tr>
                                    <td
                                        colSpan={6}
                                        className="px-4 py-10 text-center text-muted-foreground"
                                    >
                                        No contracts yet.
                                    </td>
                                </tr>
                            ) : (
                                contracts.data.map((contract) => (
                                    <tr
                                        key={contract.id}
                                        className="border-b last:border-0"
                                    >
                                        <td className="px-4 py-3">
                                            <Link
                                                href={`/admin/contracts/${contract.id}`}
                                                className="font-medium hover:underline"
                                            >
                                                {contract.company_name ||
                                                    contract.subscriber_name ||
                                                    '—'}
                                            </Link>
                                            {contract.company_name &&
                                                contract.subscriber_name && (
                                                    <p className="text-xs text-muted-foreground">
                                                        {
                                                            contract.subscriber_name
                                                        }
                                                    </p>
                                                )}
                                        </td>
                                        <td className="px-4 py-3">
                                            {contract.package_title || '—'}
                                        </td>
                                        <td className="px-4 py-3">
                                            {contract.persons}
                                        </td>
                                        <td className="px-4 py-3 font-medium">
                                            {
                                                contract.agreed_total_formatted
                                            }
                                        </td>
                                        <td className="px-4 py-3">
                                            <StatusBadge
                                                label={contract.status_label}
                                                badge={contract.status_badge}
                                            />
                                        </td>
                                        <td className="px-4 py-3">
                                            <div className="flex items-center justify-end gap-2">
                                                <StatusToggle
                                                    endpoint={`/admin/contracts/${contract.id}/status`}
                                                    status={
                                                        contract.status ===
                                                        'active'
                                                            ? 'active'
                                                            : 'inactive'
                                                    }
                                                    canToggle={canManage}
                                                    activeLabel="Deactivate contract"
                                                    inactiveLabel="Activate contract"
                                                />
                                                {canManage && (
                                                    <Button
                                                        variant="outline"
                                                        size="sm"
                                                        asChild
                                                    >
                                                        <Link
                                                            href={`/admin/contracts/${contract.id}/edit`}
                                                        >
                                                            Edit
                                                        </Link>
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
                    meta={contracts.meta}
                    pageUrl={pageUrl}
                />
            </div>
        </>
    );
}

ContractsIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Contracts', href: '/admin/contracts' },
    ],
};
