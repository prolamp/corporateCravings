import { Link } from '@inertiajs/react';
import { Head, ReportPageShell } from '@/pages/admin/reports/report-ui';
import { Button } from '@/components/ui/button';

type ReportCard = {
    key: string;
    title: string;
    description: string;
    href: string;
    permission: string;
};

type Props = {
    reports: ReportCard[];
    canManageBillSlips?: boolean;
};

export default function ReportsIndex({
    reports,
    canManageBillSlips = false,
}: Props) {
    return (
        <>
            <Head title="Reports" />
            <ReportPageShell
                title="Reports"
                description="Operational and finance summaries. Access depends on your role."
            >
                {canManageBillSlips && (
                    <div className="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-border/80 bg-card p-4">
                        <div>
                            <h3 className="font-display text-base font-semibold">
                                Bill slips
                            </h3>
                            <p className="text-sm text-muted-foreground">
                                Generate a subscriber receipt from delivered
                                days — pick a contract on the next screen
                            </p>
                        </div>
                        <Button asChild>
                            <Link href="/admin/bill-slips/create">
                                Create bill slip
                            </Link>
                        </Button>
                    </div>
                )}
                {reports.length === 0 ? (
                    <p className="rounded-xl border border-border/80 bg-card px-4 py-8 text-center text-sm text-muted-foreground">
                        No reports are available for your account.
                    </p>
                ) : (
                    <div className="grid gap-4 sm:grid-cols-2">
                        {reports.map((report) => (
                            <Link
                                key={report.key}
                                href={report.href}
                                className="block rounded-xl border border-border/80 bg-card p-5 transition-colors hover:border-foreground/20"
                            >
                                <h3 className="font-display text-lg font-semibold">
                                    {report.title}
                                </h3>
                                <p className="mt-1 text-sm text-muted-foreground">
                                    {report.description}
                                </p>
                            </Link>
                        ))}
                    </div>
                )}
            </ReportPageShell>
        </>
    );
}

ReportsIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Reports', href: '/admin/reports' },
    ],
};
