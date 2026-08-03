import { Head, Link } from '@inertiajs/react';
import { Plus, Trash2 } from 'lucide-react';
import { useMemo, useState } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';

type GroceryItem = {
    value: number;
    label: string;
    title: string;
    unit: string | null;
    unit_price: number;
    unit_price_formatted: string;
};

type Line = {
    expense_id: number | '';
    quantity: string;
};

type Props = {
    groceryOptions: GroceryItem[];
};

function formatPkr(amount: number): string {
    return `PKR ${amount.toLocaleString('en-PK', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`;
}

function round2(n: number): number {
    return Math.round(n * 100) / 100;
}

export default function CostCalculatorIndex({ groceryOptions }: Props) {
    const [yieldPortions, setYieldPortions] = useState('');
    const [lines, setLines] = useState<Line[]>([
        { expense_id: '', quantity: '' },
    ]);

    const estimate = useMemo(() => {
        const lineCosts: number[] = [];
        let total = 0;

        for (const line of lines) {
            const item = groceryOptions.find(
                (option) => option.value === Number(line.expense_id),
            );
            const qty = Number(line.quantity || 0);
            const unitPrice = item?.unit_price ?? 0;
            const cost = round2(unitPrice * qty);
            lineCosts.push(cost);
            total = round2(total + cost);
        }

        const yieldValue = Number(yieldPortions || 0);
        const perPortion =
            yieldValue > 0 ? round2(total / yieldValue) : null;

        return { total, perPortion, lineCosts };
    }, [lines, groceryOptions, yieldPortions]);

    function addLine() {
        setLines((current) => [
            ...current,
            { expense_id: '', quantity: '' },
        ]);
    }

    function updateLine(index: number, patch: Partial<Line>) {
        setLines((current) =>
            current.map((line, i) =>
                i === index ? { ...line, ...patch } : line,
            ),
        );
    }

    function removeLine(index: number) {
        setLines((current) =>
            current.length === 1
                ? current
                : current.filter((_, i) => i !== index),
        );
    }

    return (
        <>
            <Head title="Dish Cost Calculator" />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div>
                    <p className="text-xs font-bold tracking-[0.08em] text-muted-foreground uppercase">
                        Operations
                    </p>
                    <h2 className="mt-1 font-display text-2xl font-semibold tracking-tight md:text-3xl">
                        Dish cost calculator
                    </h2>
                    <p className="mt-1 max-w-2xl text-sm text-muted-foreground">
                        Build a dish batch from grocery items (chicken, rice,
                        oil…), enter how much of each you use, and get the
                        estimated cost. Optional yield gives cost per portion.
                    </p>
                </div>

                {groceryOptions.length === 0 ? (
                    <div className="rounded-xl border border-border/80 bg-card p-6 text-sm text-muted-foreground">
                        <p>
                            No grocery items yet. Add expenses under the{' '}
                            <strong className="text-foreground">
                                Groceries
                            </strong>{' '}
                            category with a title, unit (kg / litre), and unit
                            price — those items appear here for costing.
                        </p>
                        <Button className="mt-4" asChild>
                            <Link href="/admin/expenses/create">
                                Add grocery item
                            </Link>
                        </Button>
                    </div>
                ) : (
                    <div className="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
                        <section className="rounded-xl border border-border/80 bg-card p-4 md:p-6">
                            <div className="flex items-center justify-between gap-2">
                                <div>
                                    <h3 className="font-display text-lg font-semibold">
                                        Grocery items in this dish
                                    </h3>
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        Choose items, then enter weight or
                                        quantity used in the batch.
                                    </p>
                                </div>
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    onClick={addLine}
                                >
                                    <Plus className="size-4" />
                                    Add item
                                </Button>
                            </div>

                            <div className="mt-4 space-y-3">
                                {lines.map((line, index) => {
                                    const item = groceryOptions.find(
                                        (option) =>
                                            option.value ===
                                            Number(line.expense_id),
                                    );

                                    return (
                                        <div
                                            key={index}
                                            className="grid gap-3 rounded-lg border border-border/70 p-3 md:grid-cols-[1.4fr_1fr_1fr_auto]"
                                        >
                                            <div>
                                                <Label>Grocery item</Label>
                                                <select
                                                    className={cn(
                                                        selectClass,
                                                        'mt-2',
                                                    )}
                                                    value={line.expense_id}
                                                    onChange={(e) =>
                                                        updateLine(index, {
                                                            expense_id: e.target
                                                                .value
                                                                ? Number(
                                                                      e.target
                                                                          .value,
                                                                  )
                                                                : '',
                                                        })
                                                    }
                                                >
                                                    <option value="">
                                                        Select item…
                                                    </option>
                                                    {groceryOptions.map(
                                                        (option) => (
                                                            <option
                                                                key={
                                                                    option.value
                                                                }
                                                                value={
                                                                    option.value
                                                                }
                                                            >
                                                                {option.title}
                                                                {option.unit
                                                                    ? ` (${option.unit})`
                                                                    : ''}{' '}
                                                                —{' '}
                                                                {
                                                                    option.unit_price_formatted
                                                                }
                                                                {option.unit
                                                                    ? `/${option.unit}`
                                                                    : ''}
                                                            </option>
                                                        ),
                                                    )}
                                                </select>
                                            </div>
                                            <div>
                                                <Label>
                                                    Weight / qty used
                                                    {item?.unit
                                                        ? ` (${item.unit})`
                                                        : ''}
                                                </Label>
                                                <Input
                                                    className="mt-2"
                                                    type="number"
                                                    min={0}
                                                    step="0.01"
                                                    placeholder={
                                                        item?.unit === 'kg'
                                                            ? 'e.g. 2.5'
                                                            : 'e.g. 1'
                                                    }
                                                    value={line.quantity}
                                                    onChange={(e) =>
                                                        updateLine(index, {
                                                            quantity:
                                                                e.target.value,
                                                        })
                                                    }
                                                />
                                            </div>
                                            <div>
                                                <Label>Line cost</Label>
                                                <p className="mt-3 text-sm font-medium">
                                                    {formatPkr(
                                                        estimate.lineCosts[
                                                            index
                                                        ] ?? 0,
                                                    )}
                                                </p>
                                            </div>
                                            <div className="flex items-end">
                                                <Button
                                                    type="button"
                                                    variant="destructive"
                                                    size="sm"
                                                    disabled={
                                                        lines.length === 1
                                                    }
                                                    onClick={() =>
                                                        removeLine(index)
                                                    }
                                                >
                                                    <Trash2 className="size-4" />
                                                </Button>
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>

                            <div className="mt-6 grid max-w-xs gap-2">
                                <Label htmlFor="yield">
                                    Portions this batch yields (optional)
                                </Label>
                                <Input
                                    id="yield"
                                    type="number"
                                    min={0.01}
                                    step="0.01"
                                    placeholder="e.g. 50"
                                    value={yieldPortions}
                                    onChange={(e) =>
                                        setYieldPortions(e.target.value)
                                    }
                                />
                            </div>
                        </section>

                        <aside className="space-y-4">
                            <div className="rounded-xl border border-border/80 bg-muted/30 p-4 md:p-6">
                                <p className="text-[11px] font-bold tracking-[0.08em] text-muted-foreground uppercase">
                                    Estimated dish cost
                                </p>
                                <p className="mt-2 font-display text-3xl font-semibold">
                                    {formatPkr(estimate.total)}
                                </p>
                                {estimate.perPortion !== null && (
                                    <>
                                        <p className="mt-5 text-[11px] font-bold tracking-[0.08em] text-muted-foreground uppercase">
                                            Cost per portion
                                        </p>
                                        <p className="mt-2 font-display text-2xl font-semibold">
                                            {formatPkr(estimate.perPortion)}
                                        </p>
                                    </>
                                )}
                            </div>

                            <div className="rounded-xl border border-border/80 bg-card p-4 text-sm text-muted-foreground md:p-5">
                                <p className="font-medium text-foreground">
                                    Example
                                </p>
                                <p className="mt-2">
                                    Chicken 2 kg × PKR 800 + Rice 5 kg × PKR 300
                                    + Oil 0.5 L × PKR 500 ={' '}
                                    <span className="text-foreground">
                                        PKR 3,350
                                    </span>{' '}
                                    for the batch.
                                </p>
                            </div>
                        </aside>
                    </div>
                )}
            </div>
        </>
    );
}

const selectClass = cn(
    'border-input bg-background flex h-9 w-full rounded-md border px-3 py-2 text-sm shadow-xs outline-none',
    'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
);

CostCalculatorIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Dish Cost Calculator', href: '/admin/cost-calculator' },
    ],
};
