import { Link, router, useForm } from '@inertiajs/react';
import { useEffect, useMemo, useState, type FormEvent, type ReactNode } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';

export type StatusOption = { value: string; label: string };
export type SelectOption = { value: number; label: string };

export type AdminExpense = {
    id: number;
    expense_category_id: number;
    category_name: string | null;
    title: string;
    description: string | null;
    quantity: number;
    unit: string | null;
    unit_price: number;
    unit_price_formatted: string;
    total: number;
    total_formatted: string;
    date: string | null;
    date_formatted: string | null;
    status: string;
    status_label: string;
    status_badge: string;
    created_by: string | null;
    receipt_image: string | null;
    receipt_image_url: string | null;
};

type ExpenseFormProps = {
    mode: 'create' | 'edit';
    action: string;
    method?: 'post' | 'put';
    expense?: AdminExpense | null;
    statusOptions: StatusOption[];
    categoryOptions: SelectOption[];
    cancelHref: string;
};

function formatPkr(amount: number): string {
    return `PKR ${amount.toLocaleString('en-PK', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`;
}

export function ExpenseForm({
    mode,
    action,
    method = 'post',
    expense,
    statusOptions,
    categoryOptions,
    cancelHref,
}: ExpenseFormProps) {
    const form = useForm({
        expense_category_id: expense?.expense_category_id
            ? String(expense.expense_category_id)
            : '',
        title: expense?.title ?? '',
        description: expense?.description ?? '',
        quantity: String(expense?.quantity ?? 1),
        unit: expense?.unit ?? '',
        unit_price: String(expense?.unit_price ?? ''),
        date: expense?.date ?? new Date().toISOString().slice(0, 10),
        status: expense?.status ?? 'pending',
        receipt_image: null as File | null,
        remove_receipt_image: false,
        _method: method === 'put' ? 'put' : undefined,
    });

    const [previewUrl, setPreviewUrl] = useState<string | null>(null);

    useEffect(() => {
        if (!form.data.receipt_image) {
            setPreviewUrl(null);

            return;
        }

        const url = URL.createObjectURL(form.data.receipt_image);
        setPreviewUrl(url);

        return () => URL.revokeObjectURL(url);
    }, [form.data.receipt_image]);

    const existingUrl =
        !form.data.remove_receipt_image && !previewUrl
            ? (expense?.receipt_image_url ?? null)
            : null;
    const displayUrl = previewUrl ?? existingUrl;

    const liveTotal = useMemo(() => {
        const qty = Number(form.data.quantity || 0);
        const price = Number(form.data.unit_price || 0);

        return Math.round(qty * price * 100) / 100;
    }, [form.data.quantity, form.data.unit_price]);

    function submit(event: FormEvent) {
        event.preventDefault();
        form.transform((data) => ({
            ...data,
            expense_category_id: Number(data.expense_category_id),
            quantity: Number(data.quantity),
            unit_price: Number(data.unit_price),
            description: data.description || null,
            unit: data.unit || null,
        }));
        form.post(action, { forceFormData: true });
    }

    function clearReceipt() {
        form.setData((data) => ({
            ...data,
            receipt_image: null,
            remove_receipt_image: Boolean(expense?.receipt_image_url),
        }));
    }

    return (
        <form onSubmit={submit} className="space-y-6">
            <div className="grid gap-4 md:grid-cols-2">
                <Field
                    label="Category"
                    error={form.errors.expense_category_id}
                    htmlFor="expense_category_id"
                >
                    <select
                        id="expense_category_id"
                        className={selectClass}
                        value={form.data.expense_category_id}
                        onChange={(e) =>
                            form.setData('expense_category_id', e.target.value)
                        }
                        required
                    >
                        <option value="">Select…</option>
                        {categoryOptions.map((option) => (
                            <option key={option.value} value={option.value}>
                                {option.label}
                            </option>
                        ))}
                    </select>
                </Field>

                <Field label="Title" error={form.errors.title} htmlFor="title">
                    <Input
                        id="title"
                        value={form.data.title}
                        onChange={(e) => form.setData('title', e.target.value)}
                        required
                        placeholder="e.g. Chicken breast"
                    />
                </Field>

                <Field
                    label="Quantity"
                    error={form.errors.quantity}
                    htmlFor="quantity"
                >
                    <Input
                        id="quantity"
                        type="number"
                        min={0.01}
                        step="0.01"
                        value={form.data.quantity}
                        onChange={(e) =>
                            form.setData('quantity', e.target.value)
                        }
                        required
                    />
                </Field>

                <Field label="Unit" error={form.errors.unit} htmlFor="unit">
                    <Input
                        id="unit"
                        value={form.data.unit}
                        onChange={(e) => form.setData('unit', e.target.value)}
                        placeholder="kg, litre, piece…"
                    />
                </Field>

                <Field
                    label="Unit price (PKR)"
                    error={form.errors.unit_price}
                    htmlFor="unit_price"
                >
                    <Input
                        id="unit_price"
                        type="number"
                        min={0}
                        step="0.01"
                        value={form.data.unit_price}
                        onChange={(e) =>
                            form.setData('unit_price', e.target.value)
                        }
                        required
                    />
                </Field>

                <Field label="Date" error={form.errors.date} htmlFor="date">
                    <Input
                        id="date"
                        type="date"
                        value={form.data.date}
                        onChange={(e) => form.setData('date', e.target.value)}
                        required
                    />
                </Field>

                <Field
                    label="Status"
                    error={form.errors.status}
                    htmlFor="status"
                >
                    <select
                        id="status"
                        className={selectClass}
                        value={form.data.status}
                        onChange={(e) => form.setData('status', e.target.value)}
                    >
                        {statusOptions.map((option) => (
                            <option key={option.value} value={option.value}>
                                {option.label}
                            </option>
                        ))}
                    </select>
                </Field>

                <div>
                    <p className="text-[11px] font-bold tracking-[0.08em] text-muted-foreground uppercase">
                        Line total
                    </p>
                    <p className="mt-2 font-display text-2xl font-semibold">
                        {formatPkr(liveTotal)}
                    </p>
                </div>
            </div>

            <Field
                label="Notes"
                error={form.errors.description}
                htmlFor="description"
            >
                <textarea
                    id="description"
                    className={cn(selectClass, 'min-h-24 py-2')}
                    value={form.data.description}
                    onChange={(e) =>
                        form.setData('description', e.target.value)
                    }
                />
            </Field>

            <div className="grid gap-2">
                <Label htmlFor="receipt_image">Receipt image</Label>
                {displayUrl ? (
                    <div className="flex items-start gap-3">
                        <img
                            src={displayUrl}
                            alt={
                                previewUrl
                                    ? 'Selected receipt preview'
                                    : 'Current receipt'
                            }
                            className="h-28 w-28 rounded-lg border object-cover"
                        />
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            onClick={clearReceipt}
                        >
                            {previewUrl ? 'Clear selection' : 'Remove image'}
                        </Button>
                    </div>
                ) : (
                    <p className="text-sm text-muted-foreground">No receipt</p>
                )}
                <Input
                    id="receipt_image"
                    type="file"
                    accept="image/*"
                    onChange={(e) =>
                        form.setData(
                            'receipt_image',
                            e.target.files?.[0] ?? null,
                        )
                    }
                />
                <InputError message={form.errors.receipt_image} />
            </div>

            <div className="flex flex-wrap gap-3">
                <Button type="submit" disabled={form.processing}>
                    {mode === 'create' ? 'Create expense' : 'Save changes'}
                </Button>
                <Button type="button" variant="outline" asChild>
                    <Link href={cancelHref}>Cancel</Link>
                </Button>
            </div>
        </form>
    );
}

function Field({
    label,
    htmlFor,
    error,
    children,
}: {
    label: string;
    htmlFor: string;
    error?: string;
    children: ReactNode;
}) {
    return (
        <div className="grid gap-2">
            <Label htmlFor={htmlFor}>{label}</Label>
            {children}
            <InputError message={error} />
        </div>
    );
}

const selectClass = cn(
    'border-input bg-background flex h-9 w-full rounded-md border px-3 py-2 text-sm shadow-xs outline-none',
    'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
);

export function destroyExpense(id: number): void {
    if (!confirm('Delete this expense? This cannot be undone.')) {
        return;
    }

    router.delete(`/admin/expenses/${id}`);
}
