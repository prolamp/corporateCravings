import {
    BreakdownTable,
    DateRangeFilter,
    Head,
    ReportPageShell,
    ReportsBackLink,
    StatCards,
} from '@/pages/admin/reports/report-ui';

type CountRow = { code: string; label: string; count: number };

type Props = {
    filters: { date_from: string; date_to: string };
    report: {
        summary: {
            orders_count: number;
            quoted_total_formatted: string;
            paid_total_formatted: string;
        };
        by_type: CountRow[];
        by_channel: CountRow[];
        by_status: CountRow[];
    };
};

export default function OrdersReport({ filters, report }: Props) {
    return (
        <>
            <Head title="Orders report" />
            <ReportPageShell
                title="Orders report"
                description="Order volume and amounts for the selected period."
            >
                <ReportsBackLink />
                <DateRangeFilter path="/admin/reports/orders" filters={filters} />
                <StatCards
                    items={[
                        {
                            label: 'Orders',
                            value: String(report.summary.orders_count),
                        },
                        {
                            label: 'Quoted total',
                            value: report.summary.quoted_total_formatted,
                        },
                        {
                            label: 'Paid total',
                            value: report.summary.paid_total_formatted,
                        },
                    ]}
                />
                <div className="grid gap-4 lg:grid-cols-3">
                    <BreakdownTable
                        title="By type"
                        columns={['Type', 'Count']}
                        rows={report.by_type.map((row) => [
                            row.label,
                            String(row.count),
                        ])}
                    />
                    <BreakdownTable
                        title="By channel"
                        columns={['Channel', 'Count']}
                        rows={report.by_channel.map((row) => [
                            row.label,
                            String(row.count),
                        ])}
                    />
                    <BreakdownTable
                        title="By status"
                        columns={['Status', 'Count']}
                        rows={report.by_status.map((row) => [
                            row.label,
                            String(row.count),
                        ])}
                    />
                </div>
            </ReportPageShell>
        </>
    );
}

OrdersReport.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Reports', href: '/admin/reports' },
        { title: 'Orders', href: '/admin/reports/orders' },
    ],
};
