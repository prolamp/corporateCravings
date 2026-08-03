import { Link, router, useForm } from '@inertiajs/react';
import { Plus, Trash2 } from 'lucide-react';
import { useEffect, useState, type FormEvent } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';

export type StatusOption = { value: string; label: string };
export type SelectOption = { value: string | number; label: string };

export type AdminVariation = {
    id?: number | null;
    label: string;
    quantity: number | string;
    unit: string;
    price: number | string;
    type: string;
    type_label?: string;
    cartable?: boolean;
    status: string;
    status_label?: string;
    status_badge?: string;
    sort_order: number | string;
    price_formatted?: string;
};

export type AdminDish = {
    id: number;
    category_id: number;
    category_name: string | null;
    title: string;
    slug: string;
    short_description: string | null;
    image: string | null;
    image_url: string | null;
    status: string;
    status_label: string;
    status_badge: string;
    sort_order: number;
    variations_count: number;
    starting_price: number | null;
    starting_price_formatted: string | null;
    variations: AdminVariation[];
    created_at: string | null;
    updated_at: string | null;
};

type DishFormProps = {
    mode: 'create' | 'edit';
    action: string;
    method?: 'post' | 'put';
    dish?: AdminDish | null;
    categoryOptions: SelectOption[];
    statusOptions: StatusOption[];
    unitOptions: SelectOption[];
    typeOptions: SelectOption[];
    cancelHref: string;
};

function defaultVariation(sort = 1): AdminVariation {
    return {
        id: null,
        label: '1 Serving',
        quantity: '1',
        unit: 'serving',
        price: '',
        type: 'single',
        status: 'active',
        sort_order: sort,
    };
}

export function DishForm({
    mode,
    action,
    method = 'post',
    dish,
    categoryOptions,
    statusOptions,
    unitOptions,
    typeOptions,
    cancelHref,
}: DishFormProps) {
    const form = useForm({
        title: dish?.title ?? '',
        category_id: dish?.category_id
            ? String(dish.category_id)
            : categoryOptions[0]
              ? String(categoryOptions[0].value)
              : '',
        short_description: dish?.short_description ?? '',
        status: dish?.status ?? 'active',
        sort_order: String(dish?.sort_order ?? 0),
        image: null as File | null,
        remove_image: false as boolean,
        variations: (dish?.variations?.length
            ? dish.variations.map((v, i) => ({
                  id: v.id ?? null,
                  label: v.label,
                  quantity: String(v.quantity),
                  unit: v.unit,
                  price: String(v.price),
                  type: v.type,
                  status: v.status ?? 'active',
                  sort_order: String(v.sort_order ?? i + 1),
              }))
            : [defaultVariation(1), {
                  ...defaultVariation(2),
                  label: '5 KG',
                  quantity: '5',
                  unit: 'kg',
                  type: 'bulk',
              }]) as AdminVariation[],
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
            ? (dish?.image_url ?? null)
            : null;
    const displayUrl = previewUrl ?? existingUrl;

    function submit(event: FormEvent) {
        event.preventDefault();
        form.post(action, { forceFormData: true });
    }

    function clearImage() {
        form.setData((data) => ({
            ...data,
            image: null,
            remove_image: Boolean(dish?.image_url),
        }));
    }

    function onImageChange(file: File | null) {
        form.setData((data) => ({
            ...data,
            image: file,
            remove_image: false,
        }));
    }

    function updateVariation(
        index: number,
        key: keyof AdminVariation,
        value: string,
    ) {
        form.setData(
            'variations',
            form.data.variations.map((row, i) =>
                i === index ? { ...row, [key]: value } : row,
            ),
        );
    }

    function addVariation() {
        form.setData('variations', [
            ...form.data.variations,
            {
                ...defaultVariation(form.data.variations.length + 1),
                label: '',
                quantity: '',
                unit: 'kg',
                type: 'bulk',
                price: '',
            },
        ]);
    }

    function removeVariation(index: number) {
        if (form.data.variations.length <= 1) {
            return;
        }

        form.setData(
            'variations',
            form.data.variations.filter((_, i) => i !== index),
        );
    }

    const variationErrors = Object.keys(form.errors)
        .filter((key) => key.startsWith('variations'))
        .map((key) => form.errors[key])
        .filter(Boolean);

    return (
        <form onSubmit={submit} className="space-y-8">
            <section className="space-y-4">
                <h3 className="font-display text-lg font-semibold tracking-tight">
                    Basic details
                </h3>
                <div className="grid gap-4 md:grid-cols-2">
                    <Field label="Dish name" error={form.errors.title} htmlFor="title">
                        <Input
                            id="title"
                            value={form.data.title}
                            onChange={(e) =>
                                form.setData('title', e.target.value)
                            }
                            required
                            placeholder="e.g. Chicken Biryani"
                        />
                    </Field>

                    <Field
                        label="Category"
                        error={form.errors.category_id}
                        htmlFor="category_id"
                    >
                        <select
                            id="category_id"
                            className={selectClass}
                            value={form.data.category_id}
                            onChange={(e) =>
                                form.setData('category_id', e.target.value)
                            }
                            required
                        >
                            {categoryOptions.map((option) => (
                                <option
                                    key={option.value}
                                    value={String(option.value)}
                                >
                                    {option.label}
                                </option>
                            ))}
                        </select>
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
                            onChange={(e) =>
                                form.setData('status', e.target.value)
                            }
                            required
                        >
                            {statusOptions.map((option) => (
                                <option key={option.value} value={option.value}>
                                    {option.label}
                                </option>
                            ))}
                        </select>
                    </Field>

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
                                className={cn(selectClass, 'min-h-24 py-2')}
                                value={form.data.short_description}
                                onChange={(e) =>
                                    form.setData(
                                        'short_description',
                                        e.target.value,
                                    )
                                }
                                maxLength={500}
                                placeholder="Brief menu description…"
                            />
                        </Field>
                    </div>
                </div>
            </section>

            <section className="space-y-3 rounded-xl border border-border/80 p-4">
                <Label htmlFor="image">Dish image</Label>
                {displayUrl ? (
                    <div className="flex items-center gap-3">
                        <img
                            src={displayUrl}
                            alt={
                                previewUrl
                                    ? 'Selected image preview'
                                    : (dish?.title ?? 'Dish')
                            }
                            className="size-24 rounded-lg object-cover ring-1 ring-border"
                        />
                        <div className="flex flex-col gap-2">
                            {previewUrl && form.data.image && (
                                <p className="text-xs text-muted-foreground">
                                    {form.data.image.name}
                                </p>
                            )}
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                onClick={clearImage}
                            >
                                {previewUrl
                                    ? 'Clear selection'
                                    : 'Remove image'}
                            </Button>
                        </div>
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
                        onImageChange(e.target.files?.[0] ?? null)
                    }
                />
                <InputError message={form.errors.image} />
                <p className="text-xs text-muted-foreground">
                    Images are compressed to WebP automatically.
                </p>
            </section>

            <section className="space-y-4">
                <div className="flex items-end justify-between gap-3">
                    <div>
                        <h3 className="font-display text-lg font-semibold tracking-tight">
                            Portion variations
                        </h3>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Single servings are cartable. Bulk tiers are
                            WhatsApp-only.
                        </p>
                    </div>
                    <Button type="button" variant="outline" onClick={addVariation}>
                        <Plus className="size-4" />
                        Add variation
                    </Button>
                </div>

                {variationErrors.length > 0 && (
                    <div className="space-y-1">
                        {variationErrors.map((message, i) => (
                            <InputError key={i} message={message} />
                        ))}
                    </div>
                )}

                <div className="overflow-x-auto rounded-xl border border-border/80">
                    <table className="w-full min-w-[720px] text-left text-sm">
                        <thead className="bg-muted/40 text-[11px] font-bold tracking-wider text-muted-foreground uppercase">
                            <tr>
                                <th className="px-3 py-2">Label</th>
                                <th className="px-3 py-2">Qty</th>
                                <th className="px-3 py-2">Unit</th>
                                <th className="px-3 py-2">Price</th>
                                <th className="px-3 py-2">Type</th>
                                <th className="px-3 py-2">Channel</th>
                                <th className="px-3 py-2">Status</th>
                                <th className="px-3 py-2" />
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-border/60">
                            {form.data.variations.map((row, index) => (
                                <tr key={row.id ?? `new-${index}`}>
                                    <td className="px-3 py-2">
                                        <Input
                                            value={row.label}
                                            onChange={(e) =>
                                                updateVariation(
                                                    index,
                                                    'label',
                                                    e.target.value,
                                                )
                                            }
                                            required
                                            placeholder="1 Serving"
                                        />
                                    </td>
                                    <td className="px-3 py-2">
                                        <Input
                                            type="number"
                                            min={0.01}
                                            step="0.01"
                                            value={row.quantity}
                                            onChange={(e) =>
                                                updateVariation(
                                                    index,
                                                    'quantity',
                                                    e.target.value,
                                                )
                                            }
                                            required
                                        />
                                    </td>
                                    <td className="px-3 py-2">
                                        <select
                                            className={selectClass}
                                            value={row.unit}
                                            onChange={(e) =>
                                                updateVariation(
                                                    index,
                                                    'unit',
                                                    e.target.value,
                                                )
                                            }
                                        >
                                            {unitOptions.map((option) => (
                                                <option
                                                    key={String(option.value)}
                                                    value={String(option.value)}
                                                >
                                                    {option.label}
                                                </option>
                                            ))}
                                        </select>
                                    </td>
                                    <td className="px-3 py-2">
                                        <Input
                                            type="number"
                                            min={0}
                                            step="1"
                                            value={row.price}
                                            onChange={(e) =>
                                                updateVariation(
                                                    index,
                                                    'price',
                                                    e.target.value,
                                                )
                                            }
                                            required
                                            placeholder="PKR"
                                        />
                                    </td>
                                    <td className="px-3 py-2">
                                        <select
                                            className={selectClass}
                                            value={row.type}
                                            onChange={(e) =>
                                                updateVariation(
                                                    index,
                                                    'type',
                                                    e.target.value,
                                                )
                                            }
                                        >
                                            {typeOptions.map((option) => (
                                                <option
                                                    key={String(option.value)}
                                                    value={String(option.value)}
                                                >
                                                    {option.label}
                                                </option>
                                            ))}
                                        </select>
                                    </td>
                                    <td className="px-3 py-2">
                                        <span className="text-xs font-medium text-muted-foreground">
                                            {row.type === 'single'
                                                ? 'Cart'
                                                : 'WhatsApp'}
                                        </span>
                                    </td>
                                    <td className="px-3 py-2">
                                        <select
                                            className={selectClass}
                                            value={row.status}
                                            onChange={(e) =>
                                                updateVariation(
                                                    index,
                                                    'status',
                                                    e.target.value,
                                                )
                                            }
                                        >
                                            {statusOptions.map((option) => (
                                                <option
                                                    key={option.value}
                                                    value={option.value}
                                                >
                                                    {option.label}
                                                </option>
                                            ))}
                                        </select>
                                    </td>
                                    <td className="px-3 py-2 text-right">
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            disabled={
                                                form.data.variations.length <= 1
                                            }
                                            onClick={() =>
                                                removeVariation(index)
                                            }
                                            aria-label="Remove variation"
                                        >
                                            <Trash2 className="size-4" />
                                        </Button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </section>

            <div className="flex flex-wrap gap-3">
                <Button type="submit" disabled={form.processing}>
                    {mode === 'create' ? 'Create dish' : 'Save changes'}
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

export function destroyDish(id: number): void {
    if (!confirm('Delete this dish? This cannot be undone.')) {
        return;
    }

    router.delete(`/admin/dishes/${id}`);
}
