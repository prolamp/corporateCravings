import { Head, Link, router } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type DateFilters = {
    date_from: string;
    date_to: string;
};

export function ReportPageShell({
    eyebrow,
    title,
    description,
    children,
}: {
    eyebrow?: string;
    title: string;
    description: string;
    children: ReactNode;
}) {
    return (
        <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <div>
                <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                    {eyebrow ?? 'Reports'}
                </p>
                <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight md:text-3xl">
                    {title}
                </h2>
                <p className="mt-1 text-sm text-muted-foreground">
                    {description}
                </p>
            </div>
            {children}
        </div>
    );
}

export function DateRangeFilter({
    path,
    filters,
}: {
    path: string;
    filters: DateFilters;
}) {
    return (
        <form
            className="flex flex-col gap-3 rounded-xl border border-border/80 bg-card p-4 sm:flex-row sm:items-end"
            onSubmit={(event) => {
                event.preventDefault();
                const form = event.currentTarget;
                const from = (
                    form.elements.namedItem('date_from') as HTMLInputElement
                ).value;
                const to = (
                    form.elements.namedItem('date_to') as HTMLInputElement
                ).value;
                router.get(
                    path,
                    { date_from: from, date_to: to },
                    { preserveState: true, replace: true },
                );
            }}
        >
            <div className="grid flex-1 gap-3 sm:grid-cols-2">
                <div className="space-y-1.5">
                    <Label htmlFor="date_from">From</Label>
                    <Input
                        id="date_from"
                        name="date_from"
                        type="date"
                        defaultValue={filters.date_from}
                    />
                </div>
                <div className="space-y-1.5">
                    <Label htmlFor="date_to">To</Label>
                    <Input
                        id="date_to"
                        name="date_to"
                        type="date"
                        defaultValue={filters.date_to}
                    />
                </div>
            </div>
            <Button type="submit">Apply</Button>
        </form>
    );
}

export function StatCards({
    items,
}: {
    items: { label: string; value: string }[];
}) {
    return (
        <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            {items.map((item) => (
                <div
                    key={item.label}
                    className="rounded-xl border border-border/80 bg-card px-4 py-3"
                >
                    <p className="text-[11px] font-bold tracking-[0.08em] text-muted-foreground uppercase">
                        {item.label}
                    </p>
                    <p className="mt-1 font-display text-xl font-semibold tabular-nums">
                        {item.value}
                    </p>
                </div>
            ))}
        </div>
    );
}

export function BreakdownTable({
    title,
    columns,
    rows,
    empty = 'No data in this range.',
}: {
    title: string;
    columns: string[];
    rows: string[][];
    empty?: string;
}) {
    return (
        <section className="overflow-hidden rounded-xl border border-border/80 bg-card">
            <div className="border-b border-border/80 px-4 py-3">
                <h3 className="font-display text-lg font-semibold">{title}</h3>
            </div>
            {rows.length === 0 ? (
                <p className="px-4 py-8 text-center text-sm text-muted-foreground">
                    {empty}
                </p>
            ) : (
                <div className="overflow-x-auto">
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="border-b border-border/60 text-left text-muted-foreground">
                                {columns.map((column) => (
                                    <th
                                        key={column}
                                        className="px-4 py-2.5 font-medium"
                                    >
                                        {column}
                                    </th>
                                ))}
                            </tr>
                        </thead>
                        <tbody>
                            {rows.map((row, index) => (
                                <tr
                                    key={`${row[0]}-${index}`}
                                    className="border-b border-border/40 last:border-0"
                                >
                                    {row.map((cell, cellIndex) => (
                                        <td
                                            key={`${index}-${cellIndex}`}
                                            className={`px-4 py-2.5 ${cellIndex > 0 ? 'tabular-nums' : ''}`}
                                        >
                                            {cell}
                                        </td>
                                    ))}
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}
        </section>
    );
}

export function ReportsBackLink() {
    return (
        <p className="text-sm">
            <Link
                href="/admin/reports"
                className="text-muted-foreground underline-offset-4 hover:underline"
            >
                ← All reports
            </Link>
        </p>
    );
}

export { Head };
