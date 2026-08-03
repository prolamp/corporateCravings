import { Head, Link, useForm } from '@inertiajs/react';
import { FormEvent, useMemo } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';

type DeliveryOption = {
    value: number;
    label: string;
    date_formatted: string | null;
    dish_title: string | null;
    persons: number;
    total: number;
    total_formatted: string;
};

type Slip = {
    id: number;
    slip_number: string;
    company_name: string | null;
    adjustment: number;
    notes: string | null;
    items: { subscriber_schedule_id: number }[];
};

type Props = {
    slip: Slip;
    eligibleDeliveries: DeliveryOption[];
    filters: { from: string | null; to: string | null };
};

export default function BillSlipsEdit({
    slip,
    eligibleDeliveries,
}: Props) {
    const form = useForm({
        schedule_ids: slip.items.map((i) => i.subscriber_schedule_id),
        adjustment: String(slip.adjustment ?? 0),
        notes: slip.notes ?? '',
    });

    const selectedTotal = useMemo(() => {
        const set = new Set(form.data.schedule_ids);
        return eligibleDeliveries
            .filter((d) => set.has(d.value))
            .reduce((sum, d) => sum + d.total, 0);
    }, [eligibleDeliveries, form.data.schedule_ids]);

    const adjustment = Number(form.data.adjustment || 0);
    const grandTotal = selectedTotal + (Number.isFinite(adjustment) ? adjustment : 0);

    function toggle(id: number) {
        const set = new Set(form.data.schedule_ids);
        if (set.has(id)) set.delete(id);
        else set.add(id);
        form.setData('schedule_ids', Array.from(set));
    }

    function submit(e: FormEvent) {
        e.preventDefault();
        form.put(`/admin/bill-slips/${slip.id}`);
    }

    function formatPkr(n: number) {
        return `PKR ${n.toLocaleString('en-PK', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        })}`;
    }

    return (
        <>
            <Head title={`Edit ${slip.slip_number}`} />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div>
                    <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                        Packages
                    </p>
                    <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight">
                        Edit {slip.slip_number}
                    </h2>
                    <p className="mt-1 text-sm text-muted-foreground">
                        {slip.company_name}
                    </p>
                </div>

                <form onSubmit={submit} className="flex flex-col gap-6">
                    <div className="rounded-xl border border-border/80 bg-card p-4 md:p-6">
                        <h3 className="font-display text-lg font-semibold">
                            Delivered days
                        </h3>
                        {form.errors.schedule_ids && (
                            <p className="mt-2 text-sm text-destructive">
                                {form.errors.schedule_ids}
                            </p>
                        )}
                        <ul className="mt-4 divide-y">
                            {eligibleDeliveries.map((day) => {
                                const checked = form.data.schedule_ids.includes(
                                    day.value,
                                );
                                return (
                                    <li key={day.value}>
                                        <button
                                            type="button"
                                            onClick={() => toggle(day.value)}
                                            className={cn(
                                                'flex w-full items-center gap-3 py-3 text-left',
                                                checked
                                                    ? 'bg-muted/40'
                                                    : 'hover:bg-muted/20',
                                            )}
                                        >
                                            <span
                                                className={cn(
                                                    'flex size-5 shrink-0 items-center justify-center rounded border text-[10px]',
                                                    checked
                                                        ? 'border-foreground bg-foreground text-background'
                                                        : 'border-input',
                                                )}
                                            >
                                                {checked ? '✓' : ''}
                                            </span>
                                            <span className="min-w-0 flex-1">
                                                <span className="block font-medium">
                                                    {day.date_formatted}
                                                </span>
                                                <span className="block text-sm text-muted-foreground">
                                                    {day.dish_title} ·{' '}
                                                    {day.persons} persons
                                                </span>
                                            </span>
                                            <span className="font-medium">
                                                {day.total_formatted}
                                            </span>
                                        </button>
                                    </li>
                                );
                            })}
                        </ul>
                    </div>

                    <div className="grid gap-4 rounded-xl border border-border/80 bg-card p-4 md:grid-cols-2 md:p-6">
                        <div>
                            <Label htmlFor="adjustment">Adjustment (PKR)</Label>
                            <Input
                                id="adjustment"
                                className="mt-1"
                                type="number"
                                step="0.01"
                                value={form.data.adjustment}
                                onChange={(e) =>
                                    form.setData('adjustment', e.target.value)
                                }
                            />
                        </div>
                        <div>
                            <Label htmlFor="notes">Notes</Label>
                            <Input
                                id="notes"
                                className="mt-1"
                                value={form.data.notes}
                                onChange={(e) =>
                                    form.setData('notes', e.target.value)
                                }
                            />
                        </div>
                        <div className="md:col-span-2 flex flex-wrap items-end justify-between gap-4 border-t pt-4">
                            <p className="font-display text-xl font-semibold">
                                Total {formatPkr(grandTotal)}
                            </p>
                            <div className="flex gap-2">
                                <Button variant="outline" asChild>
                                    <Link href={`/admin/bill-slips/${slip.id}`}>
                                        Cancel
                                    </Link>
                                </Button>
                                <Button
                                    type="submit"
                                    disabled={
                                        form.processing ||
                                        form.data.schedule_ids.length === 0
                                    }
                                >
                                    Save changes
                                </Button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </>
    );
}

BillSlipsEdit.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Bill slips', href: '/admin/bill-slips' },
        { title: 'Edit', href: '#' },
    ],
};
