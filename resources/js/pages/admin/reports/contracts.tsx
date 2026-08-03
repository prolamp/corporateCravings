import { StatusBadge } from '@/components/status-badge/status-badge';
import {
    BreakdownTable,
    Head,
    ReportPageShell,
    ReportsBackLink,
    StatCards,
} from '@/pages/admin/reports/report-ui';

type CountRow = { code: string; label: string; count: number };

type ContractRow = {
    id: number;
    subscriber: string | null;
    package: string | null;
    agreed_total_formatted: string;
    status: string;
    status_label: string;
    status_badge: string;
};

type Props = {
    report: {
        summary: {
            contracts_count: number;
            active_count: number;
            active_agreed_total_formatted: string;
        };
        by_status: CountRow[];
        active_rows: ContractRow[];
    };
};

export default function ContractsReport({ report }: Props) {
    return (
        <>
            <Head title="Contracts report" />
            <ReportPageShell
                title="Contracts report"
                description="Subscriber contracts and active agreed rates."
            >
                <ReportsBackLink />
                <StatCards
                    items={[
                        {
                            label: 'All contracts',
                            value: String(report.summary.contracts_count),
                        },
                        {
                            label: 'Active',
                            value: String(report.summary.active_count),
                        },
                        {
                            label: 'Active agreed total',
                            value: report.summary.active_agreed_total_formatted,
                        },
                    ]}
                />
                <BreakdownTable
                    title="By status"
                    columns={['Status', 'Count']}
                    rows={report.by_status.map((row) => [
                        row.label,
                        String(row.count),
                    ])}
                />
                <section className="overflow-hidden rounded-xl border border-border/80 bg-card">
                    <div className="border-b border-border/80 px-4 py-3">
                        <h3 className="font-display text-lg font-semibold">
                            Active contracts
                        </h3>
                    </div>
                    {report.active_rows.length === 0 ? (
                        <p className="px-4 py-8 text-center text-sm text-muted-foreground">
                            No active contracts.
                        </p>
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="w-full text-sm">
                                <thead>
                                    <tr className="border-b border-border/60 text-left text-muted-foreground">
                                        <th className="px-4 py-2.5 font-medium">
                                            Subscriber
                                        </th>
                                        <th className="px-4 py-2.5 font-medium">
                                            Package
                                        </th>
                                        <th className="px-4 py-2.5 font-medium">
                                            Agreed
                                        </th>
                                        <th className="px-4 py-2.5 font-medium">
                                            Status
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {report.active_rows.map((row) => (
                                        <tr
                                            key={row.id}
                                            className="border-b border-border/40 last:border-0"
                                        >
                                            <td className="px-4 py-2.5">
                                                {row.subscriber ?? '—'}
                                            </td>
                                            <td className="px-4 py-2.5">
                                                {row.package ?? '—'}
                                            </td>
                                            <td className="px-4 py-2.5 tabular-nums">
                                                {row.agreed_total_formatted}
                                            </td>
                                            <td className="px-4 py-2.5">
                                                <StatusBadge
                                                    label={row.status_label}
                                                    badge={row.status_badge}
                                                />
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}
                </section>
            </ReportPageShell>
        </>
    );
}

ContractsReport.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Reports', href: '/admin/reports' },
        { title: 'Contracts', href: '/admin/reports/contracts' },
    ],
};
