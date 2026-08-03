import { Link, router, useForm } from '@inertiajs/react';
import type { FormEvent, ReactNode } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';

export type StatusOption = { value: string; label: string };

export type AdminExpenseCategory = {
    id: number;
    name: string;
    description: string | null;
    is_active: boolean;
    status: string;
    status_label: string;
    status_badge: string;
    expenses_count: number;
    created_at: string | null;
    updated_at: string | null;
};

type CategoryFormProps = {
    mode: 'create' | 'edit';
    action: string;
    method?: 'post' | 'put';
    category?: AdminExpenseCategory | null;
    statusOptions: StatusOption[];
    cancelHref: string;
};

export function ExpenseCategoryForm({
    mode,
    action,
    method = 'post',
    category,
    statusOptions,
    cancelHref,
}: CategoryFormProps) {
    const form = useForm({
        name: category?.name ?? '',
        description: category?.description ?? '',
        is_active: category?.is_active ?? true,
    });

    function submit(event: FormEvent) {
        event.preventDefault();
        form.transform((data) => ({
            ...data,
            description: data.description || null,
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
                <Field label="Name" error={form.errors.name} htmlFor="name">
                    <Input
                        id="name"
                        value={form.data.name}
                        onChange={(e) => form.setData('name', e.target.value)}
                        required
                        placeholder="e.g. Groceries"
                    />
                </Field>

                <Field
                    label="Status"
                    error={form.errors.is_active}
                    htmlFor="is_active"
                >
                    <select
                        id="is_active"
                        className={selectClass}
                        value={form.data.is_active ? 'active' : 'inactive'}
                        onChange={(e) =>
                            form.setData(
                                'is_active',
                                e.target.value === 'active',
                            )
                        }
                    >
                        {statusOptions.map((option) => (
                            <option key={option.value} value={option.value}>
                                {option.label}
                            </option>
                        ))}
                    </select>
                </Field>
            </div>

            <Field
                label="Description"
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
                    placeholder="Optional notes for staff"
                />
            </Field>

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

export function destroyExpenseCategory(id: number): void {
    if (!confirm('Delete this expense category? This cannot be undone.')) {
        return;
    }

    router.delete(`/admin/expense-categories/${id}`);
}
