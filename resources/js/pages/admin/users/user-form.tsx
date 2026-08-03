import { Link, router, useForm } from '@inertiajs/react';
import { useEffect, useState, type FormEvent } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';

export type RoleOption = { value: string; label: string };
export type StatusOption = { value: string; label: string };

export type AdminUser = {
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
    created_at: string | null;
    updated_at: string | null;
    can_update?: boolean;
    can_delete?: boolean;
};

type UserFormProps = {
    mode: 'create' | 'edit';
    action: string;
    method?: 'post' | 'put';
    user?: AdminUser | null;
    roleOptions: RoleOption[];
    statusOptions: StatusOption[];
    cancelHref: string;
};

export function UserForm({
    mode,
    action,
    method = 'post',
    user,
    roleOptions,
    statusOptions,
    cancelHref,
}: UserFormProps) {
    const form = useForm({
        name: user?.name ?? '',
        email: user?.email ?? '',
        phone: user?.phone ?? '',
        password: '',
        address: user?.address ?? '',
        company_name: user?.company_name ?? '',
        status: user?.status ?? 'active',
        role: user?.role ?? roleOptions[0]?.value ?? '',
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
        !form.data.remove_image && !previewUrl ? (user?.image_url ?? null) : null;
    const displayUrl = previewUrl ?? existingUrl;

    function submit(event: FormEvent) {
        event.preventDefault();

        form.post(action, { forceFormData: true });
    }

    function clearImage() {
        form.setData((data) => ({
            ...data,
            image: null,
            remove_image: Boolean(user?.image_url),
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
                <Field
                    label="Full name"
                    error={form.errors.name}
                    htmlFor="name"
                >
                    <Input
                        id="name"
                        value={form.data.name}
                        onChange={(e) => form.setData('name', e.target.value)}
                        required
                        autoComplete="name"
                    />
                </Field>

                <Field label="Phone" error={form.errors.phone} htmlFor="phone">
                    <Input
                        id="phone"
                        value={form.data.phone}
                        onChange={(e) => form.setData('phone', e.target.value)}
                        required
                        autoComplete="tel"
                        placeholder="03XXXXXXXXX"
                    />
                </Field>

                <Field
                    label="Email (optional)"
                    error={form.errors.email}
                    htmlFor="email"
                >
                    <Input
                        id="email"
                        type="email"
                        value={form.data.email}
                        onChange={(e) => form.setData('email', e.target.value)}
                        autoComplete="email"
                    />
                </Field>

                <Field
                    label={
                        mode === 'create'
                            ? 'Password (optional — defaults to phone)'
                            : 'New password (optional)'
                    }
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
                    label="Company"
                    error={form.errors.company_name}
                    htmlFor="company_name"
                >
                    <Input
                        id="company_name"
                        value={form.data.company_name}
                        onChange={(e) =>
                            form.setData('company_name', e.target.value)
                        }
                    />
                </Field>

                <Field
                    label="Address"
                    error={form.errors.address}
                    htmlFor="address"
                >
                    <Input
                        id="address"
                        value={form.data.address}
                        onChange={(e) =>
                            form.setData('address', e.target.value)
                        }
                    />
                </Field>

                <Field label="Role" error={form.errors.role} htmlFor="role">
                    <select
                        id="role"
                        className={selectClass}
                        value={form.data.role}
                        onChange={(e) => form.setData('role', e.target.value)}
                        required
                    >
                        {roleOptions.map((option) => (
                            <option key={option.value} value={option.value}>
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
                <Label htmlFor="image">Profile image</Label>
                {displayUrl ? (
                    <div className="flex items-center gap-3">
                        <img
                            src={displayUrl}
                            alt={
                                previewUrl
                                    ? 'Selected image preview'
                                    : (user?.name ?? 'Profile')
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
                                {previewUrl ? 'Clear selection' : 'Remove image'}
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
                    Images are compressed to WebP automatically.
                </p>
            </div>

            <div className="flex flex-wrap gap-3">
                <Button type="submit" disabled={form.processing}>
                    {mode === 'create' ? 'Create user' : 'Save changes'}
                </Button>
                <Button type="button" variant="outline" asChild>
                    <Link href={cancelHref}>Cancel</Link>
                </Button>
                {mode === 'edit' && form.isDirty && (
                    <Button
                        type="button"
                        variant="ghost"
                        onClick={() => form.reset()}
                    >
                        Reset
                    </Button>
                )}
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

export function destroyUser(id: number): void {
    if (!confirm('Delete this user? This cannot be undone.')) {
        return;
    }

    router.delete(`/admin/users/${id}`);
}
