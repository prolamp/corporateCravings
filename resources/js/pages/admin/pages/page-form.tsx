import { Link, router, useForm } from '@inertiajs/react';
import type { FormEvent, ReactNode } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';

export type StatusOption = { value: string; label: string };

export type AdminPage = {
    id: number;
    title: string;
    slug: string;
    body: string | null;
    meta_title: string | null;
    meta_description: string | null;
    status: string;
    status_label: string;
    status_badge: string;
    updated_at_formatted: string | null;
};

type PageFormProps = {
    mode: 'create' | 'edit';
    action: string;
    method?: 'post' | 'put';
    page?: AdminPage | null;
    statusOptions: StatusOption[];
    cancelHref: string;
};

export function CmsPageForm({
    mode,
    action,
    method = 'post',
    page,
    statusOptions,
    cancelHref,
}: PageFormProps) {
    const form = useForm({
        title: page?.title ?? '',
        slug: page?.slug ?? '',
        body: page?.body ?? '',
        meta_title: page?.meta_title ?? '',
        meta_description: page?.meta_description ?? '',
        status: page?.status ?? 'published',
    });

    function submit(event: FormEvent) {
        event.preventDefault();
        form.transform((data) => ({
            ...data,
            slug: data.slug.trim() || null,
            body: data.body || null,
            meta_title: data.meta_title || null,
            meta_description: data.meta_description || null,
        }));

        if (method === 'put') {
            form.put(action);
        } else {
            form.post(action);
        }
    }

    return (
        <form onSubmit={submit} className="space-y-6">
            <div className="grid gap-4 md:grid-cols-2">
                <Field label="Title" error={form.errors.title} htmlFor="title">
                    <Input
                        id="title"
                        value={form.data.title}
                        onChange={(e) => form.setData('title', e.target.value)}
                        required
                        placeholder="Privacy Policy"
                    />
                </Field>
                <Field
                    label="Slug"
                    error={form.errors.slug}
                    htmlFor="slug"
                >
                    <Input
                        id="slug"
                        value={form.data.slug}
                        onChange={(e) => form.setData('slug', e.target.value)}
                        placeholder="auto from title if blank"
                    />
                    <p className="mt-1 text-xs text-muted-foreground">
                        Public URL path, e.g. privacy-policy → /privacy-policy
                    </p>
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
                <Field
                    label="Meta title"
                    error={form.errors.meta_title}
                    htmlFor="meta_title"
                >
                    <Input
                        id="meta_title"
                        value={form.data.meta_title}
                        onChange={(e) =>
                            form.setData('meta_title', e.target.value)
                        }
                        placeholder="SEO title"
                    />
                </Field>
            </div>

            <Field
                label="Meta description"
                error={form.errors.meta_description}
                htmlFor="meta_description"
            >
                <textarea
                    id="meta_description"
                    className={cn(selectClass, 'min-h-20 py-2')}
                    value={form.data.meta_description}
                    onChange={(e) =>
                        form.setData('meta_description', e.target.value)
                    }
                    maxLength={500}
                />
            </Field>

            <Field label="Body" error={form.errors.body} htmlFor="body">
                <textarea
                    id="body"
                    className={cn(selectClass, 'min-h-56 py-2 font-mono text-xs')}
                    value={form.data.body}
                    onChange={(e) => form.setData('body', e.target.value)}
                    placeholder="HTML or plain text content"
                />
            </Field>

            <div className="flex flex-wrap gap-3">
                <Button type="submit" disabled={form.processing}>
                    {mode === 'create' ? 'Create page' : 'Save changes'}
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

export function destroyPage(id: number): void {
    if (!confirm('Delete this page? This cannot be undone.')) {
        return;
    }

    router.delete(`/admin/pages/${id}`);
}

export function togglePageStatus(id: number): void {
    router.patch(`/admin/pages/${id}/status`, {}, { preserveScroll: true });
}
