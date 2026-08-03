import { Link, router, useForm } from '@inertiajs/react';
import { useEffect, useState, type FormEvent } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';

export type StatusOption = { value: string; label: string };

export type SubscriberContractSummary = {
    id: number;
    package_id: number;
    package_title: string | null;
    meal_type: string;
    persons: number;
    delivery_time: string | null;
    package_amount: number;
    package_amount_formatted: string;
    discount: number;
    discount_formatted: string;
    agreed_total: number;
    agreed_total_formatted: string;
    status: string;
    status_label: string;
    status_badge: string;
    starts_on: string | null;
    ends_on: string | null;
    notes: string | null;
};

export type AdminSubscriber = {
    id: number;
    name: string;
    email: string | null;
    phone: string | null;
    address: string | null;
    company_name: string | null;
    image: string | null;
    image_url: string | null;
    status: string;
    status_label: string;
    status_badge: string;
    role: string | null;
    role_label: string | null;
    contract: SubscriberContractSummary | null;
    created_at: string | null;
    updated_at: string | null;
    can_update?: boolean;
    can_delete?: boolean;
};

type SubscriberFormProps = {
    mode: 'create' | 'edit';
    action: string;
    method?: 'post' | 'put';
    subscriber?: AdminSubscriber | null;
    statusOptions: StatusOption[];
    cancelHref: string;
};

export function SubscriberForm({
    mode,
    action,
    method = 'post',
    subscriber,
    statusOptions,
    cancelHref,
}: SubscriberFormProps) {
    const form = useForm({
        name: subscriber?.name ?? '',
        company_name: subscriber?.company_name ?? '',
        email: subscriber?.email ?? '',
        phone: subscriber?.phone ?? '',
        password: '',
        address: subscriber?.address ?? '',
        status: subscriber?.status ?? 'active',
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
            ? (subscriber?.image_url ?? null)
            : null;
    const displayUrl = previewUrl ?? existingUrl;

    function submit(event: FormEvent) {
        event.preventDefault();
        form.post(action, { forceFormData: true });
    }

    return (
        <form onSubmit={submit} className="space-y-6">
            <div className="grid gap-4 md:grid-cols-2">
                <Field
                    label="Company name"
                    error={form.errors.company_name}
                    htmlFor="company_name"
                >
                    <Input
                        id="company_name"
                        value={form.data.company_name}
                        onChange={(e) =>
                            form.setData('company_name', e.target.value)
                        }
                        required
                        placeholder="e.g. Factory Foods Co"
                    />
                </Field>

                <Field
                    label="Contact name"
                    error={form.errors.name}
                    htmlFor="name"
                >
                    <Input
                        id="name"
                        value={form.data.name}
                        onChange={(e) => form.setData('name', e.target.value)}
                        required
                    />
                </Field>

                <Field label="Phone" error={form.errors.phone} htmlFor="phone">
                    <Input
                        id="phone"
                        value={form.data.phone}
                        onChange={(e) => form.setData('phone', e.target.value)}
                        required
                        placeholder="03XXXXXXXXX"
                    />
                    {mode === 'create' && (
                        <p className="mt-1 text-xs text-muted-foreground">
                            Default password is the phone number if left blank.
                        </p>
                    )}
                </Field>

                <Field label="Email" error={form.errors.email} htmlFor="email">
                    <Input
                        id="email"
                        type="email"
                        value={form.data.email}
                        onChange={(e) => form.setData('email', e.target.value)}
                    />
                </Field>

                <Field
                    label={mode === 'create' ? 'Password (optional)' : 'New password'}
                    error={form.errors.password}
                    htmlFor="password"
                >
                    <Input
                        id="password"
                        type="password"
                        value={form.data.password}
                        onChange={(e) =>
                            form.setData('password', e.target.value)
                        }
                        autoComplete="new-password"
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

                <div className="md:col-span-2">
                    <Field
                        label="Address"
                        error={form.errors.address}
                        htmlFor="address"
                    >
                        <textarea
                            id="address"
                            className={cn(selectClass, 'min-h-20 py-2')}
                            value={form.data.address}
                            onChange={(e) =>
                                form.setData('address', e.target.value)
                            }
                        />
                    </Field>
                </div>
            </div>

            <section className="space-y-3 rounded-xl border border-border/80 p-4">
                <Label htmlFor="image">Company / contact image</Label>
                {displayUrl ? (
                    <div className="flex items-center gap-3">
                        <img
                            src={displayUrl}
                            alt=""
                            className="size-20 rounded-lg object-cover ring-1 ring-border"
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
                                        subscriber?.image_url,
                                    ),
                                }))
                            }
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
                        form.setData((data) => ({
                            ...data,
                            image: e.target.files?.[0] ?? null,
                            remove_image: false,
                        }))
                    }
                />
                <InputError message={form.errors.image} />
            </section>

            <div className="flex flex-wrap gap-3">
                <Button type="submit" disabled={form.processing}>
                    {mode === 'create'
                        ? 'Create subscriber'
                        : 'Save changes'}
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

export function destroySubscriber(id: number): void {
    if (!confirm('Delete this subscriber? This cannot be undone.')) {
        return;
    }

    router.delete(`/admin/subscribers/${id}`);
}
