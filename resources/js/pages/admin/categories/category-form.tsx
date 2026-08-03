import { Link, router, useForm } from '@inertiajs/react';
import { useEffect, useState, type FormEvent } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';

export type StatusOption = { value: string; label: string };

export type AdminCategory = {
    id: number;
    name: string;
    slug: string;
    image: string | null;
    image_url: string | null;
    status: string;
    status_label: string;
    status_badge: string;
    sort_order: number;
    dishes_count: number;
    created_at: string | null;
    updated_at: string | null;
};

type CategoryFormProps = {
    mode: 'create' | 'edit';
    action: string;
    method?: 'post' | 'put';
    category?: AdminCategory | null;
    statusOptions: StatusOption[];
    cancelHref: string;
};

export function CategoryForm({
    mode,
    action,
    method = 'post',
    category,
    statusOptions,
    cancelHref,
}: CategoryFormProps) {
    const form = useForm({
        name: category?.name ?? '',
        status: category?.status ?? 'active',
        sort_order: String(category?.sort_order ?? 0),
        image: null as File | null,
        remove_image: false as boolean,
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
            ? (category?.image_url ?? null)
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
            remove_image: Boolean(category?.image_url),
        }));
    }

    function onImageChange(file: File | null) {
        form.setData((data) => ({
            ...data,
            image: file,
            remove_image: false,
        }));
    }

    return (
        <form onSubmit={submit} className="space-y-6">
            <div className="grid gap-4 md:grid-cols-2">
                <Field label="Name" error={form.errors.name} htmlFor="name">
                    <Input
                        id="name"
                        value={form.data.name}
                        onChange={(e) => form.setData('name', e.target.value)}
                        required
                        placeholder="e.g. Gourmet Dishes"
                    />
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
            </div>

            <div className="space-y-3 rounded-xl border border-border/80 p-4">
                <Label htmlFor="image">Category image</Label>
                {displayUrl ? (
                    <div className="flex items-center gap-3">
                        <img
                            src={displayUrl}
                            alt={
                                previewUrl
                                    ? 'Selected image preview'
                                    : (category?.name ?? 'Category')
                            }
                            className="size-20 rounded-lg object-cover ring-1 ring-border"
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
                <p className="text-xs text-muted-foreground">
                    Images are compressed to WebP automatically. Replacing or
                    deleting removes the old file.
                </p>
            </div>

            <div className="flex flex-wrap gap-3">
                <Button type="submit" disabled={form.processing}>
                    {mode === 'create' ? 'Create category' : 'Save changes'}
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

export function destroyCategory(id: number): void {
    if (!confirm('Delete this category? This cannot be undone.')) {
        return;
    }

    router.delete(`/admin/categories/${id}`);
}
