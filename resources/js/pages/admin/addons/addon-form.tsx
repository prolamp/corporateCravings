import { Link, router, useForm } from '@inertiajs/react';
import { useEffect, useState, type FormEvent } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';

export type StatusOption = { value: string; label: string };
export type SelectOption = { value: string | number; label: string };

export type AdminAddon = {
    id: number;
    title: string;
    slug: string;
    short_description: string | null;
    type: string;
    type_label: string;
    price: number;
    price_formatted: string;
    image: string | null;
    image_url: string | null;
    is_global: boolean;
    status: string;
    status_label: string;
    status_badge: string;
    sort_order: number;
    dishes_count: number;
    dish_ids: number[];
    dish_names: string[];
    created_at: string | null;
    updated_at: string | null;
};

type AddonFormProps = {
    mode: 'create' | 'edit';
    action: string;
    method?: 'post' | 'put';
    addon?: AdminAddon | null;
    typeOptions: SelectOption[];
    statusOptions: StatusOption[];
    dishOptions: SelectOption[];
    cancelHref: string;
};

export function AddonForm({
    mode,
    action,
    method = 'post',
    addon,
    typeOptions,
    statusOptions,
    dishOptions,
    cancelHref,
}: AddonFormProps) {
    const form = useForm({
        title: addon?.title ?? '',
        short_description: addon?.short_description ?? '',
        type: addon?.type ?? 'extra',
        price: String(addon?.price ?? ''),
        is_global: addon?.is_global ?? true,
        status: addon?.status ?? 'active',
        sort_order: String(addon?.sort_order ?? 0),
        image: null as File | null,
        remove_image: false as boolean,
        dish_ids: (addon?.dish_ids ?? []).map(String),
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
            ? (addon?.image_url ?? null)
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
            remove_image: Boolean(addon?.image_url),
        }));
    }

    function onImageChange(file: File | null) {
        form.setData((data) => ({
            ...data,
            image: file,
            remove_image: false,
        }));
    }

    function toggleDish(id: string) {
        const current = form.data.dish_ids;
        form.setData(
            'dish_ids',
            current.includes(id)
                ? current.filter((value) => value !== id)
                : [...current, id],
        );
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
                        placeholder="e.g. Raita"
                    />
                </Field>

                <Field label="Type" error={form.errors.type} htmlFor="type">
                    <select
                        id="type"
                        className={selectClass}
                        value={form.data.type}
                        onChange={(e) => form.setData('type', e.target.value)}
                        required
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
                </Field>

                <Field label="Price (PKR)" error={form.errors.price} htmlFor="price">
                    <Input
                        id="price"
                        type="number"
                        min={0}
                        step="1"
                        value={form.data.price}
                        onChange={(e) => form.setData('price', e.target.value)}
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
                <div className="flex items-center justify-between gap-3">
                    <div>
                        <Label htmlFor="is_global">Availability</Label>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Global addons appear on every dish. Linked addons
                            only show on selected dishes.
                        </p>
                    </div>
                    <label className="flex items-center gap-2 text-sm font-medium">
                        <input
                            id="is_global"
                            type="checkbox"
                            checked={form.data.is_global}
                            onChange={(e) =>
                                form.setData('is_global', e.target.checked)
                            }
                            className="size-4 rounded border"
                        />
                        Global
                    </label>
                </div>
                <InputError message={form.errors.is_global} />

                {!form.data.is_global && (
                    <div className="max-h-56 space-y-2 overflow-y-auto rounded-lg border border-border/60 p-3">
                        {dishOptions.length === 0 ? (
                            <p className="text-sm text-muted-foreground">
                                No dishes available to link.
                            </p>
                        ) : (
                            dishOptions.map((dish) => {
                                const id = String(dish.value);
                                const checked =
                                    form.data.dish_ids.includes(id);

                                return (
                                    <label
                                        key={id}
                                        className="flex items-center gap-2 text-sm"
                                    >
                                        <input
                                            type="checkbox"
                                            checked={checked}
                                            onChange={() => toggleDish(id)}
                                            className="size-4 rounded border"
                                        />
                                        {dish.label}
                                    </label>
                                );
                            })
                        )}
                        <InputError message={form.errors.dish_ids} />
                    </div>
                )}
            </section>

            <section className="space-y-3 rounded-xl border border-border/80 p-4">
                <Label htmlFor="image">Addon image</Label>
                {displayUrl ? (
                    <div className="flex items-center gap-3">
                        <img
                            src={displayUrl}
                            alt={
                                previewUrl
                                    ? 'Selected image preview'
                                    : (addon?.title ?? 'Addon')
                            }
                            className="size-20 rounded-lg object-cover ring-1 ring-border"
                        />
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            onClick={clearImage}
                        >
                            {previewUrl ? 'Clear selection' : 'Remove image'}
                        </Button>
                    </div>
                ) : (
                    <div className="flex size-20 items-center justify-center rounded-lg border border-dashed border-border text-xs text-muted-foreground">
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
            </section>

            <div className="flex flex-wrap gap-3">
                <Button type="submit" disabled={form.processing}>
                    {mode === 'create' ? 'Create addon' : 'Save changes'}
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

export function destroyAddon(id: number): void {
    if (!confirm('Delete this addon? This cannot be undone.')) {
        return;
    }

    router.delete(`/admin/addons/${id}`);
}
