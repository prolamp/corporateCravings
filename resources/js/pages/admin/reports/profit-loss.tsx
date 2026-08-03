import {
    BreakdownTable,
    DateRangeFilter,
    Head,
    ReportPageShell,
    ReportsBackLink,
    StatCards,
} from '@/pages/admin/reports/report-ui';

type AmountRow = {
    category?: string;
    label?: string;
    amount_formatted: string;
};

type Props = {
    filters: { date_from: string; date_to: string };
    report: {
        summary: {
            revenue_formatted: string;
            expenses_formatted: string;
            net_formatted: string;
        };
        expense_by_category: AmountRow[];
        earnings_by_type: AmountRow[];
    };
};

export default function ProfitLossReport({ filters, report }: Props) {
    return (
        <>
            <Head title="Profit & loss" />
            <ReportPageShell
                title="Profit & loss"
                description="Paid revenue versus expenses for the selected period."
            >
                <ReportsBackLink />
                <DateRangeFilter
                    path="/admin/reports/profit-loss"
                    filters={filters}
                />
                <StatCards
                    items={[
                        {
                            label: 'Revenue (paid)',
                            value: report.summary.revenue_formatted,
                        },
                        {
                            label: 'Expenses',
                            value: report.summary.expenses_formatted,
                        },
                        {
                            label: 'Net',
                            value: report.summary.net_formatted,
                        },
                    ]}
                />
                <div className="grid gap-4 lg:grid-cols-2">
                    <BreakdownTable
                        title="Revenue by order type"
                        columns={['Type', 'Amount']}
                        rows={report.earnings_by_type.map((row) => [
                            row.label ?? '—',
                            row.amount_formatted,
                        ])}
                    />
                    <BreakdownTable
                        title="Expenses by category"
                        columns={['Category', 'Amount']}
                        rows={report.expense_by_category.map((row) => [
                            row.category ?? '—',
                            row.amount_formatted,
                        ])}
                    />
                </div>
            </ReportPageShell>
        </>
    );
}

ProfitLossReport.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Reports', href: '/admin/reports' },
        { title: 'Profit & loss', href: '/admin/reports/profit-loss' },
    ],
};
