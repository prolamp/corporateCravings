import { Head, Link, router, useForm } from '@inertiajs/react';
import { FormEvent, useMemo } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';

type SelectOption = { value: number; label: string };

type DeliveryOption = {
    value: number;
    label: string;
    date: string | null;
    date_formatted: string | null;
    dish_title: string | null;
    persons: number;
    total: number;
    total_formatted: string;
};

type Props = {
    contractOptions: SelectOption[];
    selectedContractId: number | null;
    filters: {
        from: string | null;
        to: string | null;
        user_id: number | null;
    };
    eligibleDeliveries: DeliveryOption[];
    contract: {
        id: number;
        label: string;
        user_id: number;
        company_name: string | null;
    } | null;
};

export default function BillSlipsCreate({
    contractOptions,
    selectedContractId,
    filters,
    eligibleDeliveries,
    contract,
}: Props) {
    const form = useForm({
        subscribe_contract_id: selectedContractId
            ? String(selectedContractId)
            : '',
        schedule_ids: [] as number[],
        adjustment: '0',
        notes: '',
    });

    const selectedTotal = useMemo(() => {
        const set = new Set(form.data.schedule_ids);
        return eligibleDeliveries
            .filter((d) => set.has(d.value))
            .reduce((sum, d) => sum + d.total, 0);
    }, [eligibleDeliveries, form.data.schedule_ids]);

    const adjustment = Number(form.data.adjustment || 0);
    const grandTotal = selectedTotal + (Number.isFinite(adjustment) ? adjustment : 0);

    function reload(params: Record<string, string | number | null | undefined>) {
        router.get(
            '/admin/bill-slips/create',
            {
                contract_id:
                    params.contract_id ??
                    form.data.subscribe_contract_id ??
                    selectedContractId ??
                    '',
                user_id: params.user_id ?? filters.user_id ?? '',
                from: params.from ?? filters.from ?? '',
                to: params.to ?? filters.to ?? '',
                last_month: params.last_month ? 1 : '',
            },
            { preserveState: false, replace: true },
        );
    }

    function toggle(id: number) {
        const set = new Set(form.data.schedule_ids);
        if (set.has(id)) {
            set.delete(id);
        } else {
            set.add(id);
        }
        form.setData('schedule_ids', Array.from(set));
    }

    function toggleAll() {
        if (form.data.schedule_ids.length === eligibleDeliveries.length) {
            form.setData('schedule_ids', []);
        } else {
            form.setData(
                'schedule_ids',
                eligibleDeliveries.map((d) => d.value),
            );
        }
    }

    function submit(e: FormEvent) {
        e.preventDefault();
        form.post('/admin/bill-slips');
    }

    function formatPkr(n: number) {
        return `PKR ${n.toLocaleString('en-PK', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        })}`;
    }

    return (
        <>
            <Head title="Create bill slip" />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div>
                    <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                        Packages
                    </p>
                    <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight">
                        Create bill slip
                    </h2>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Pick a contract and delivered days to generate a receipt
                    </p>
                </div>

                <form onSubmit={submit} className="flex flex-col gap-6">
                    <div className="grid gap-4 rounded-xl border border-border/80 bg-card p-4 md:grid-cols-2 md:p-6">
                        <div className="md:col-span-2">
                            <Label htmlFor="contract">Contract</Label>
                            <select
                                id="contract"
                                className="mt-1 flex h-9 w-full rounded-md border border-input bg-background px-3 text-sm"
                                value={form.data.subscribe_contract_id}
                                onChange={(e) => {
                                    form.setData(
                                        'subscribe_contract_id',
                                        e.target.value,
                                    );
                                    form.setData('schedule_ids', []);
                                    reload({ contract_id: e.target.value });
                                }}
                            >
                                <option value="">Select contract…</option>
                                {contractOptions.map((opt) => (
                                    <option key={opt.value} value={opt.value}>
                                        {opt.label}
                                    </option>
                                ))}
                            </select>
                            {form.errors.subscribe_contract_id && (
                                <p className="mt-1 text-sm text-destructive">
                                    {form.errors.subscribe_contract_id}
                                </p>
                            )}
                        </div>

                        <div>
                            <Label htmlFor="from">From</Label>
                            <Input
                                id="from"
                                type="date"
                                className="mt-1"
                                value={filters.from ?? ''}
                                onChange={(e) =>
                                    reload({ from: e.target.value || null })
                                }
                            />
                        </div>
                        <div>
                            <Label htmlFor="to">To</Label>
                            <Input
                                id="to"
                                type="date"
                                className="mt-1"
                                value={filters.to ?? ''}
                                onChange={(e) =>
                                    reload({ to: e.target.value || null })
                                }
                            />
                        </div>
                        <div className="md:col-span-2">
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                onClick={() => reload({ last_month: 1 })}
                            >
                                Last month
                            </Button>
                        </div>
                    </div>

                    <div className="rounded-xl border border-border/80 bg-card p-4 md:p-6">
                        <div className="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h3 className="font-display text-lg font-semibold">
                                    Delivered days
                                </h3>
                                <p className="text-sm text-muted-foreground">
                                    {contract
                                        ? contract.company_name
                                        : 'Select a contract to load eligible days'}
                                </p>
                            </div>
                            {eligibleDeliveries.length > 0 && (
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    onClick={toggleAll}
                                >
                                    {form.data.schedule_ids.length ===
                                    eligibleDeliveries.length
                                        ? 'Clear all'
                                        : 'Select all'}
                                </Button>
                            )}
                        </div>

                        {form.errors.schedule_ids && (
                            <p className="mt-3 text-sm text-destructive">
                                {form.errors.schedule_ids}
                            </p>
                        )}

                        {!form.data.subscribe_contract_id ? (
                            <p className="mt-4 text-sm text-muted-foreground">
                                Choose a contract first.
                            </p>
                        ) : eligibleDeliveries.length === 0 ? (
                            <p className="mt-4 text-sm text-muted-foreground">
                                No eligible delivered days in this range (or
                                they are already on another slip).
                            </p>
                        ) : (
                            <ul className="mt-4 divide-y">
                                {eligibleDeliveries.map((day) => {
                                    const checked =
                                        form.data.schedule_ids.includes(
                                            day.value,
                                        );
                                    return (
                                        <li key={day.value}>
                                            <button
                                                type="button"
                                                onClick={() => toggle(day.value)}
                                                className={cn(
                                                    'flex w-full items-center gap-3 py-3 text-left transition-colors',
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
                        )}
                    </div>

                    <div className="grid gap-4 rounded-xl border border-border/80 bg-card p-4 md:grid-cols-2 md:p-6">
                        <div>
                            <Label htmlFor="adjustment">
                                Adjustment (PKR)
                            </Label>
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
                            <p className="mt-1 text-xs text-muted-foreground">
                                Negative values apply deductions
                            </p>
                            {form.errors.adjustment && (
                                <p className="mt-1 text-sm text-destructive">
                                    {form.errors.adjustment}
                                </p>
                            )}
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
                            <div className="text-sm">
                                <p className="text-muted-foreground">
                                    Subtotal {formatPkr(selectedTotal)}
                                    {adjustment !== 0
                                        ? ` · Adj ${formatPkr(adjustment)}`
                                        : ''}
                                </p>
                                <p className="mt-1 font-display text-xl font-semibold">
                                    Total {formatPkr(grandTotal)}
                                </p>
                            </div>
                            <div className="flex gap-2">
                                <Button variant="outline" asChild>
                                    <Link href="/admin/bill-slips">Cancel</Link>
                                </Button>
                                <Button
                                    type="submit"
                                    disabled={
                                        form.processing ||
                                        form.data.schedule_ids.length === 0
                                    }
                                >
                                    Generate slip
                                </Button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </>
    );
}

BillSlipsCreate.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Bill slips', href: '/admin/bill-slips' },
        { title: 'Create', href: '/admin/bill-slips/create' },
    ],
};
