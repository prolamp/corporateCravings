import { StatusBadge } from '@/components/status-badge/status-badge';

export type ScheduleEntry = {
    id: number;
    date: string | null;
    date_formatted: string | null;
    dish_title: string | null;
    package_title: string | null;
    persons: number;
    unit_price_formatted: string;
    total_formatted: string;
    status: string;
    status_label: string;
    status_badge: string;
    payment_status?: string;
    payment_status_label?: string;
    payment_status_badge?: string;
    notes: string | null;
};

export function ScheduleRow({ entry }: { entry: ScheduleEntry }) {
    return (
        <li className="rounded-2xl border border-border/80 bg-card p-4 md:p-5">
            <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div className="min-w-0">
                    <div className="flex flex-wrap items-center gap-2">
                        <p className="font-semibold tracking-tight">
                            {entry.date_formatted ?? entry.date ?? '—'}
                        </p>
                        <StatusBadge
                            label={entry.status_label}
                            badge={entry.status_badge}
                        />
                        {entry.payment_status_label &&
                            entry.payment_status_badge && (
                                <StatusBadge
                                    label={entry.payment_status_label}
                                    badge={entry.payment_status_badge}
                                />
                            )}
                    </div>
                    <p className="mt-1 text-sm text-muted-foreground">
                        {entry.dish_title || 'Menu TBD'}
                        {entry.package_title ? ` · ${entry.package_title}` : ''}
                    </p>
                    <p className="mt-1 text-sm text-muted-foreground">
                        {entry.persons} persons · {entry.unit_price_formatted} /
                        person
                    </p>
                    {entry.notes && (
                        <p className="mt-2 text-sm text-muted-foreground">
                            {entry.notes}
                        </p>
                    )}
                </div>
                <p className="shrink-0 font-display text-lg font-bold text-primary tabular-nums">
                    {entry.total_formatted}
                </p>
            </div>
        </li>
    );
}
