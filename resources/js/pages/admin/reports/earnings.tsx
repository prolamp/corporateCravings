import {
    BreakdownTable,
    DateRangeFilter,
    Head,
    ReportPageShell,
    ReportsBackLink,
    StatCards,
} from '@/pages/admin/reports/report-ui';

type AmountRow = {
    code?: string;
    label?: string;
    date?: string;
    amount_formatted: string;
};

type Props = {
    filters: { date_from: string; date_to: string };
    report: {
        summary: {
            paid_orders: number;
            paid_total_formatted: string;
        };
        by_type: AmountRow[];
        by_day: AmountRow[];
    };
};

export default function EarningsReport({ filters, report }: Props) {
    return (
        <>
            <Head title="Earnings report" />
            <ReportPageShell
                title="Earnings"
                description="Paid order revenue (amount paid) for the selected period."
            >
                <ReportsBackLink />
                <DateRangeFilter
                    path="/admin/reports/earnings"
                    filters={filters}
                />
                <StatCards
                    items={[
                        {
                            label: 'Paid orders',
                            value: String(report.summary.paid_orders),
                        },
                        {
                            label: 'Paid total',
                            value: report.summary.paid_total_formatted,
                        },
                    ]}
                />
                <div className="grid gap-4 lg:grid-cols-2">
                    <BreakdownTable
                        title="By order type"
                        columns={['Type', 'Amount']}
                        rows={report.by_type.map((row) => [
                            row.label ?? '—',
                            row.amount_formatted,
                        ])}
                    />
                    <BreakdownTable
                        title="By day"
                        columns={['Date', 'Amount']}
                        rows={report.by_day.map((row) => [
                            row.date ?? '—',
                            row.amount_formatted,
                        ])}
                    />
                </div>
            </ReportPageShell>
        </>
    );
}

EarningsReport.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Reports', href: '/admin/reports' },
        { title: 'Earnings', href: '/admin/reports/earnings' },
    ],
};
