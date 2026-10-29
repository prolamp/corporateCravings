import { Link, router, useForm } from '@inertiajs/react';
import { useEffect, useMemo, useState, type FormEvent } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';

export type StatusOption = { value: string; label: string };

export type DishOption = {
    value: number;
    label: string;
    unit_price: number;
    unit_price_formatted: string;
    dish_variation_id: number;
};

export type PackageItemRow = {
    id?: number;
    dish_id: number;
    dish_title?: string | null;
    dish_variation_id?: number | null;
    unit_price?: number;
    unit_price_formatted?: string;
    sort_order?: number;
};

export type AdminPackage = {
    id: number;
    title: string;
    slug: string;
    short_description: string | null;
    image: string | null;
    image_url: string | null;
    base_amount_per_person: number;
    base_amount_per_person_formatted: string;
    discount_amount: number;
    discount_percent: number;
    total_amount_per_person: number;
    total_amount_per_person_formatted: string;
    status: string;
    status_label: string;
    status_badge: string;
    show_on_website: boolean;
    sort_order: number;
    items_count: number;
    items: PackageItemRow[];
    created_at: string | null;
    updated_at: string | null;
};

type PackageFormProps = {
    mode: 'create' | 'edit';
    action: string;
    method?: 'post' | 'put';
    packageModel?: AdminPackage | null;
    statusOptions: StatusOption[];
    dishOptions: DishOption[];
    cancelHref: string;
};

function formatPkr(amount: number): string {
    return `PKR ${amount.toLocaleString('en-PK', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`;
}

export function PackageForm({
    mode,
    action,
    method = 'post',
    packageModel,
    statusOptions,
    dishOptions,
    cancelHref,
}: PackageFormProps) {
    const form = useForm({
        title: packageModel?.title ?? '',
        short_description: packageModel?.short_description ?? '',
        status: packageModel?.status ?? 'active',
        show_on_website: packageModel?.show_on_website ?? true,
        sort_order: String(packageModel?.sort_order ?? 0),
        discount_amount: String(packageModel?.discount_amount ?? 0),
        discount_percent: String(packageModel?.discount_percent ?? 0),
        image: null as File | null,
        remove_image: false as boolean,
        items: (packageModel?.items?.length
            ? packageModel.items.map((item) => ({
                  dish_id: item.dish_id,
                  dish_variation_id: item.dish_variation_id ?? null,
              }))
            : []) as Array<{
            dish_id: number;
            dish_variation_id: number | null;
        }>,
        _method: method === 'put' ? 'put' : undefined,
    });

    const [previewUrl, setPreviewUrl] = useState<string | null>(null);

    useEffect(() => {
        if (!form.data.image) {
            setPreviewUrl(null);

            return;
        }

        const url = URL.createObjectURL(form.data.image);
        setPreviewUrl(url);

        return () => URL.revokeObjectURL(url);
    }, [form.data.image]);

    const existingUrl =
        !form.data.remove_image && !previewUrl
            ? (packageModel?.image_url ?? null)
            : null;
    const displayUrl = previewUrl ?? existingUrl;

    const selectedIds = new Set(form.data.items.map((item) => item.dish_id));

    const pricing = useMemo(() => {
        const dishSum = form.data.items.reduce((sum, item) => {
            const option = dishOptions.find((d) => d.value === item.dish_id);
            return sum + (option?.unit_price ?? 0);
        }, 0);
        const count = form.data.items.length;
        const base = count > 0 ? dishSum / count : 0;
        const amountOff = Number(form.data.discount_amount || 0);
        const percentOff = (base * Number(form.data.discount_percent || 0)) / 100;
        const total = Math.max(0, base - amountOff - percentOff);

        return { dishSum, count, base, total };
    }, [
        form.data.items,
        form.data.discount_amount,
        form.data.discount_percent,
        dishOptions,
    ]);

    function submit(event: FormEvent) {
        event.preventDefault();
        form.transform((data) => ({
            ...data,
            items:
                typeof data.items === 'string'
                    ? data.items
                    : JSON.stringify(data.items),
        }));
        form.post(action, { forceFormData: true });
    }

    function toggleDish(option: DishOption) {
        if (selectedIds.has(option.value)) {
            form.setData(
                'items',
                form.data.items.filter((item) => item.dish_id !== option.value),
            );

            return;
        }

        form.setData('items', [
            ...form.data.items,
            {
                dish_id: option.value,
                dish_variation_id: option.dish_variation_id,
            },
        ]);
    }

    function moveItem(index: number, direction: -1 | 1) {
        const next = [...form.data.items];
        const target = index + direction;
        if (target < 0 || target >= next.length) {
            return;
        }
        [next[index], next[target]] = [next[target], next[index]];
        form.setData('items', next);
    }

    return (
        <form onSubmit={submit} className="space-y-8">
            <div className="grid gap-4 md:grid-cols-2">
                <Field label="Title" error={form.errors.title} htmlFor="title">
                    <Input
                        id="title"
                        value={form.data.title}
                        onChange={(e) => form.setData('title', e.target.value)}
                        required
                        placeholder="e.g. Office Lunch Combo"
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
                        required
                    >
                        {statusOptions.map((option) => (
                            <option key={option.value} value={option.value}>
                                {option.label}
                            </option>
                        ))}
                    </select>
                </Field>

                <div className="flex items-center justify-between gap-3 rounded-lg border border-border/60 px-3 py-3 md:col-span-2">
                    <div>
                        <Label htmlFor="show_on_website">Show on website</Label>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Turn off for on-demand or private packages. They
                            stay available in admin and subscriber contracts.
                        </p>
                    </div>
                    <label className="flex items-center gap-2 text-sm font-medium">
                        <input
                            id="show_on_website"
                            type="checkbox"
                            checked={form.data.show_on_website}
                            onChange={(e) =>
                                form.setData(
                                    'show_on_website',
                                    e.target.checked,
                                )
                            }
                            className="size-4 rounded border"
                        />
                        Public
                    </label>
                </div>
                <InputError message={form.errors.show_on_website} />

                <Field
                    label="Sort order"
                    error={form.errors.sort_order}
                    htmlFor="sort_order"
                >
                    <Input
                        id="sort_order"
                        type="number"
                        min={0}
                        value={form.data.sort_order}
                        onChange={(e) =>
                            form.setData('sort_order', e.target.value)
                        }
                    />
                </Field>

                <div className="md:col-span-2">
                    <Field
                        label="Short description"
                        error={form.errors.short_description}
                        htmlFor="short_description"
                    >
                        <textarea
                            id="short_description"
                            className={cn(selectClass, 'min-h-20 py-2')}
                            value={form.data.short_description}
                            onChange={(e) =>
                                form.setData(
                                    'short_description',
                                    e.target.value,
                                )
                            }
                            maxLength={500}
                        />
                    </Field>
                </div>
            </div>

            <section className="space-y-3 rounded-xl border border-border/80 p-4">
                <Label htmlFor="image">Package image</Label>
                {displayUrl ? (
                    <div className="flex items-center gap-3">
                        <img
                            src={displayUrl}
                            alt={
                                previewUrl
                                    ? 'Selected image preview'
                                    : (packageModel?.title ?? 'Package')
                            }
                            className="size-24 rounded-lg object-cover ring-1 ring-border"
                        />
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            onClick={() =>
                                form.setData((data) => ({
                                    ...data,
                                    image: null,
                                    remove_image: Boolean(
                                        packageModel?.image_url,
                                    ),
                                }))
                            }
                        >
                            {previewUrl ? 'Clear selection' : 'Remove image'}
                        </Button>
                    </div>
                ) : (
                    <div className="flex size-24 items-center justify-center rounded-lg border border-dashed border-border text-xs text-muted-foreground">
                        No image
                    </div>
                )}
                <Input
                    id="image"
                    type="file"
                    accept="image/*"
                    onChange={(e) =>
                        form.setData((data) => ({
                            ...data,
                            image: e.target.files?.[0] ?? null,
                            remove_image: false,
                        }))
                    }
                />
                <InputError message={form.errors.image} />
            </section>

            <section className="space-y-4">
                <div>
                    <h3 className="font-display text-lg font-semibold tracking-tight">
                        Included dishes
                    </h3>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Dishes rotate across the month. Per-person base is the
                        average of selected dish prices (sum ÷ dish count).
                    </p>
                </div>
                <InputError message={form.errors.items} />

                <div className="max-h-64 space-y-2 overflow-y-auto rounded-xl border border-border/80 p-3">
                    {dishOptions.length === 0 ? (
                        <p className="text-sm text-muted-foreground">
                            No dishes with single-serving prices yet.
                        </p>
                    ) : (
                        dishOptions.map((dish) => (
                            <label
                                key={dish.value}
                                className="flex items-center justify-between gap-3 text-sm"
                            >
                                <span className="flex items-center gap-2">
                                    <input
                                        type="checkbox"
                                        checked={selectedIds.has(dish.value)}
                                        onChange={() => toggleDish(dish)}
                                        className="size-4 rounded border"
                                    />
                                    {dish.label}
                                </span>
                                <span className="text-muted-foreground">
                                    {dish.unit_price_formatted}
                                </span>
                            </label>
                        ))
                    )}
                </div>

                {form.data.items.length > 0 && (
                    <div className="overflow-hidden rounded-xl border border-border/80">
                        <table className="w-full text-left text-sm">
                            <thead className="bg-muted/40 text-[11px] font-bold tracking-wider text-muted-foreground uppercase">
                                <tr>
                                    <th className="px-3 py-2">Order</th>
                                    <th className="px-3 py-2">Dish</th>
                                    <th className="px-3 py-2">Dish price</th>
                                    <th className="px-3 py-2" />
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-border/60">
                                {form.data.items.map((item, index) => {
                                    const option = dishOptions.find(
                                        (d) => d.value === item.dish_id,
                                    );

                                    return (
                                        <tr key={item.dish_id}>
                                            <td className="px-3 py-2">
                                                {index + 1}
                                            </td>
                                            <td className="px-3 py-2 font-medium">
                                                {option?.label ??
                                                    `Dish #${item.dish_id}`}
                                            </td>
                                            <td className="px-3 py-2">
                                                {option?.unit_price_formatted ??
                                                    '—'}
                                            </td>
                                            <td className="px-3 py-2 text-right">
                                                <div className="flex justify-end gap-1">
                                                    <Button
                                                        type="button"
                                                        variant="ghost"
                                                        size="sm"
                                                        onClick={() =>
                                                            moveItem(index, -1)
                                                        }
                                                        disabled={index === 0}
                                                    >
                                                        Up
                                                    </Button>
                                                    <Button
                                                        type="button"
                                                        variant="ghost"
                                                        size="sm"
                                                        onClick={() =>
                                                            moveItem(index, 1)
                                                        }
                                                        disabled={
                                                            index ===
                                                            form.data.items
                                                                .length -
                                                                1
                                                        }
                                                    >
                                                        Down
                                                    </Button>
                                                </div>
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>
                )}
            </section>

            <section className="grid gap-4 rounded-xl border border-border/80 p-4 md:grid-cols-2">
                <Field
                    label="Discount amount (PKR)"
                    error={form.errors.discount_amount}
                    htmlFor="discount_amount"
                >
                    <Input
                        id="discount_amount"
                        type="number"
                        min={0}
                        step="1"
                        value={form.data.discount_amount}
                        onChange={(e) =>
                            form.setData('discount_amount', e.target.value)
                        }
                    />
                </Field>
                <Field
                    label="Discount percent"
                    error={form.errors.discount_percent}
                    htmlFor="discount_percent"
                >
                    <Input
                        id="discount_percent"
                        type="number"
                        min={0}
                        max={100}
                        step="0.01"
                        value={form.data.discount_percent}
                        onChange={(e) =>
                            form.setData('discount_percent', e.target.value)
                        }
                    />
                </Field>

                <div className="rounded-lg bg-muted/40 p-3 md:col-span-2">
                    <p className="text-sm text-muted-foreground">
                        Dish sum{' '}
                        <span className="font-medium text-foreground">
                            {formatPkr(pricing.dishSum)}
                        </span>
                        {pricing.count > 0 && (
                            <>
                                {' '}
                                ÷ {pricing.count} = avg base / person{' '}
                                <span className="font-medium text-foreground">
                                    {formatPkr(pricing.base)}
                                </span>
                            </>
                        )}
                        {' · '}
                        Public total / person{' '}
                        <span className="font-medium text-foreground">
                            {formatPkr(pricing.total)}
                        </span>
                    </p>
                </div>
            </section>

            <div className="flex flex-wrap gap-3">
                <Button type="submit" disabled={form.processing}>
                    {mode === 'create' ? 'Create package' : 'Save changes'}
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
    children: React.ReactNode;
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

export function destroyPackage(id: number): void {
    if (!confirm('Delete this package? This cannot be undone.')) {
        return;
    }

    router.delete(`/admin/packages/${id}`);
}
