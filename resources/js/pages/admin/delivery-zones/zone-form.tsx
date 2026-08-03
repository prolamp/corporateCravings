import { Link, router, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';

export type StatusOption = { value: string; label: string };

export type AdminDeliveryZone = {
    id: number;
    name: string;
    fee: number;
    fee_formatted: string;
    is_active: boolean;
    status: string;
    status_label: string;
    status_badge: string;
    sort_order: number;
    orders_count: number;
    created_at: string | null;
    updated_at: string | null;
};

type ZoneFormProps = {
    mode: 'create' | 'edit';
    action: string;
    method?: 'post' | 'put';
    zone?: AdminDeliveryZone | null;
    statusOptions: StatusOption[];
    cancelHref: string;
};

export function DeliveryZoneForm({
    mode,
    action,
    method = 'post',
    zone,
    statusOptions,
    cancelHref,
}: ZoneFormProps) {
    const form = useForm({
        name: zone?.name ?? '',
        fee: String(zone?.fee ?? ''),
        is_active: zone?.is_active ?? true,
        sort_order: String(zone?.sort_order ?? 0),
        _method: method === 'put' ? 'put' : undefined,
    });

    function submit(event: FormEvent) {
        event.preventDefault();
        form.post(action, { forceFormData: true });
    }

    return (
        <form onSubmit={submit} className="space-y-6">
            <div className="grid gap-4 md:grid-cols-2">
                <Field label="Zone name" error={form.errors.name} htmlFor="name">
                    <Input
                        id="name"
                        value={form.data.name}
                        onChange={(e) => form.setData('name', e.target.value)}
                        required
                        placeholder="e.g. I-8"
                    />
                </Field>

                <Field label="Delivery fee (PKR)" error={form.errors.fee} htmlFor="fee">
                    <Input
                        id="fee"
                        type="number"
                        min={0}
                        step="1"
                        value={form.data.fee}
                        onChange={(e) => form.setData('fee', e.target.value)}
                        required
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
            </div>

            <div className="flex flex-wrap gap-3">
                <Button type="submit" disabled={form.processing}>
                    {mode === 'create' ? 'Create zone' : 'Save changes'}
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

export function destroyDeliveryZone(id: number): void {
    if (!confirm('Delete this delivery zone? This cannot be undone.')) {
        return;
    }

    router.delete(`/admin/delivery-zones/${id}`);
}
