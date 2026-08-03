import { Link, useForm } from '@inertiajs/react';
import { Plus, Trash2 } from 'lucide-react';
import { useMemo, type FormEvent, type ReactNode } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';
import type { StatusOption } from '@/pages/admin/orders/index';

export type ZoneOption = {
    value: number;
    label: string;
    fee: number;
    fee_formatted: string;
};

export type VariationOption = {
    value: number;
    label: string;
    dish_id: number;
    dish_title: string | null;
    price: number;
    price_formatted: string;
    type: string;
};

export type AddonOption = {
    value: number;
    label: string;
    price: number;
    price_formatted: string;
};

type LineAddon = { addon_id: number; quantity: number };
type LineItem = {
    dish_variation_id: number | '';
    quantity: number;
    unit_price: string;
    addons: LineAddon[];
};

type OrderFormProps = {
    mode: 'create' | 'edit';
    action: string;
    method?: 'post' | 'put';
    order?: {
        type: string;
        status: string;
        customer_name: string | null;
        customer_phone: string | null;
        customer_email: string | null;
        delivery_address: string | null;
        delivery_zone?: string | null;
        delivery_fee: number;
        discount_amount: number;
        discount_percent: number;
        amount_paid: number | null;
        notes: string | null;
        delivery_person_name: string | null;
        items: Array<{
            dish_variation_id?: number | null;
            quantity: number;
            unit_price: number;
            addons: Array<{
                addon_id?: number | null;
                quantity: number;
                title: string;
            }>;
        }>;
    } | null;
    deliveryZoneId?: number | null;
    statusOptions: StatusOption[];
    typeOptions: StatusOption[];
    zoneOptions: ZoneOption[];
    variationOptions: VariationOption[];
    addonOptions: AddonOption[];
    cancelHref: string;
};

function formatPkr(amount: number): string {
    return `PKR ${amount.toLocaleString('en-PK', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`;
}

function emptyLine(): LineItem {
    return {
        dish_variation_id: '',
        quantity: 1,
        unit_price: '',
        addons: [],
    };
}

export function OrderForm({
    mode,
    action,
    method = 'post',
    order,
    deliveryZoneId = null,
    statusOptions,
    typeOptions,
    zoneOptions,
    variationOptions,
    addonOptions,
    cancelHref,
}: OrderFormProps) {
    const form = useForm({
        type: order?.type ?? 'event',
        status: order?.status ?? 'pending',
        customer_name: order?.customer_name ?? '',
        customer_phone: order?.customer_phone ?? '',
        customer_email: order?.customer_email ?? '',
        delivery_address: order?.delivery_address ?? '',
        delivery_zone_id: deliveryZoneId ? String(deliveryZoneId) : '',
        delivery_fee: String(order?.delivery_fee ?? ''),
        discount_amount: String(order?.discount_amount ?? 0),
        discount_percent: String(order?.discount_percent ?? 0),
        amount_paid:
            order?.amount_paid !== null && order?.amount_paid !== undefined
                ? String(order.amount_paid)
                : '',
        notes: order?.notes ?? '',
        delivery_person_name: order?.delivery_person_name ?? '',
        items: (order?.items?.length
            ? order.items.map((item) => ({
                  dish_variation_id: (item.dish_variation_id ??
                      '') as number | '',
                  quantity: item.quantity,
                  unit_price: String(item.unit_price),
                  addons: (item.addons ?? [])
                      .filter((addon) => addon.addon_id)
                      .map((addon) => ({
                          addon_id: Number(addon.addon_id),
                          quantity: addon.quantity || 1,
                      })),
              }))
            : [emptyLine()]) as LineItem[],
    });

    const totals = useMemo(() => {
        let list = 0;
        for (const item of form.data.items) {
            const variation = variationOptions.find(
                (option) => option.value === Number(item.dish_variation_id),
            );
            const unit =
                item.unit_price !== ''
                    ? Number(item.unit_price)
                    : (variation?.price ?? 0);
            let line = unit * Number(item.quantity || 0);
            for (const addon of item.addons) {
                const addonOpt = addonOptions.find(
                    (option) => option.value === addon.addon_id,
                );
                line += (addonOpt?.price ?? 0) * Number(addon.quantity || 1);
            }
            list += line;
        }

        const discountAmount = Number(form.data.discount_amount || 0);
        const discountPercent = Number(form.data.discount_percent || 0);
        const discount =
            discountAmount > 0
                ? discountAmount
                : (list * discountPercent) / 100;
        const zone = zoneOptions.find(
            (option) => String(option.value) === form.data.delivery_zone_id,
        );
        const fee =
            form.data.delivery_fee !== ''
                ? Number(form.data.delivery_fee)
                : (zone?.fee ?? 0);
        const subtotal = Math.max(0, list - discount);
        const quoted = subtotal + fee;

        return { list, discount, fee, subtotal, quoted };
    }, [form.data, variationOptions, addonOptions, zoneOptions]);

    function onZoneChange(zoneId: string) {
        form.setData('delivery_zone_id', zoneId);
        const zone = zoneOptions.find(
            (option) => String(option.value) === zoneId,
        );
        if (zone) {
            form.setData('delivery_fee', String(zone.fee));
        }
    }

    function onVariationChange(index: number, variationId: string) {
        const variation = variationOptions.find(
            (option) => String(option.value) === variationId,
        );
        const items = [...form.data.items];
        items[index] = {
            ...items[index],
            dish_variation_id: variationId ? Number(variationId) : '',
            unit_price: variation ? String(variation.price) : '',
        };
        form.setData('items', items);
    }

    function submit(event: FormEvent) {
        event.preventDefault();
        form.transform((data) => ({
            ...data,
            delivery_zone_id: data.delivery_zone_id
                ? Number(data.delivery_zone_id)
                : null,
            delivery_fee:
                data.delivery_fee === '' ? null : Number(data.delivery_fee),
            discount_amount: Number(data.discount_amount || 0),
            discount_percent: Number(data.discount_percent || 0),
            amount_paid:
                data.amount_paid === '' ? null : Number(data.amount_paid),
            customer_phone: data.customer_phone || null,
            customer_email: data.customer_email || null,
            delivery_address: data.delivery_address || null,
            notes: data.notes || null,
            delivery_person_name: data.delivery_person_name || null,
            items: data.items.map((item) => ({
                dish_variation_id: Number(item.dish_variation_id),
                quantity: Number(item.quantity),
                unit_price:
                    item.unit_price === '' ? null : Number(item.unit_price),
                addons: item.addons.map((addon) => ({
                    addon_id: addon.addon_id,
                    quantity: addon.quantity,
                })),
            })),
        }));

        if (method === 'put') {
            form.put(action);
        } else {
            form.post(action);
        }
    }

    return (
        <form onSubmit={submit} className="space-y-6">
            {form.hasErrors && (
                <div
                    className="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900"
                    role="alert"
                >
                    Please fix the highlighted fields and try again.
                </div>
            )}

            <div className="grid gap-4 md:grid-cols-2">
                <Field label="Type" error={form.errors.type} htmlFor="type">
                    <select
                        id="type"
                        className={selectClass}
                        value={form.data.type}
                        onChange={(e) => form.setData('type', e.target.value)}
                    >
                        {typeOptions.map((option) => (
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
                        onChange={(e) =>
                            form.setData('status', e.target.value)
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
                    label="Customer name"
                    error={form.errors.customer_name}
                    htmlFor="customer_name"
                >
                    <Input
                        id="customer_name"
                        value={form.data.customer_name}
                        onChange={(e) =>
                            form.setData('customer_name', e.target.value)
                        }
                        required
                    />
                </Field>
                <Field
                    label="Phone"
                    error={form.errors.customer_phone}
                    htmlFor="customer_phone"
                >
                    <Input
                        id="customer_phone"
                        value={form.data.customer_phone}
                        onChange={(e) =>
                            form.setData('customer_phone', e.target.value)
                        }
                    />
                </Field>
                <Field
                    label="Email"
                    error={form.errors.customer_email}
                    htmlFor="customer_email"
                >
                    <Input
                        id="customer_email"
                        type="email"
                        value={form.data.customer_email}
                        onChange={(e) =>
                            form.setData('customer_email', e.target.value)
                        }
                    />
                </Field>
                <Field
                    label="Delivery zone"
                    error={form.errors.delivery_zone_id}
                    htmlFor="delivery_zone_id"
                >
                    <select
                        id="delivery_zone_id"
                        className={selectClass}
                        value={form.data.delivery_zone_id}
                        onChange={(e) => onZoneChange(e.target.value)}
                    >
                        <option value="">No zone</option>
                        {zoneOptions.map((option) => (
                            <option key={option.value} value={option.value}>
                                {option.label} · {option.fee_formatted}
                            </option>
                        ))}
                    </select>
                </Field>
            </div>

            <Field
                label="Delivery address"
                error={form.errors.delivery_address}
                htmlFor="delivery_address"
            >
                <textarea
                    id="delivery_address"
                    className={cn(selectClass, 'min-h-20 py-2')}
                    value={form.data.delivery_address}
                    onChange={(e) =>
                        form.setData('delivery_address', e.target.value)
                    }
                />
            </Field>

            <div className="rounded-xl border border-border/80 bg-card p-4">
                <div className="flex items-center justify-between gap-2">
                    <h3 className="font-display text-lg font-semibold">
                        Line items
                    </h3>
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        onClick={() =>
                            form.setData('items', [
                                ...form.data.items,
                                emptyLine(),
                            ])
                        }
                    >
                        <Plus className="size-4" />
                        Add item
                    </Button>
                </div>
                <InputError message={form.errors.items} className="mt-2" />

                <div className="mt-4 space-y-4">
                    {form.data.items.map((item, index) => (
                        <div
                            key={index}
                            className="grid gap-3 rounded-lg border border-border/70 p-3 md:grid-cols-[1fr_100px_120px_auto]"
                        >
                            <div>
                                <Label>Variation</Label>
                                <select
                                    className={cn(selectClass, 'mt-2')}
                                    value={item.dish_variation_id}
                                    onChange={(e) =>
                                        onVariationChange(index, e.target.value)
                                    }
                                    required
                                >
                                    <option value="">Select…</option>
                                    {variationOptions.map((option) => (
                                        <option
                                            key={option.value}
                                            value={option.value}
                                        >
                                            {option.label} ·{' '}
                                            {option.price_formatted}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <div>
                                <Label>Qty</Label>
                                <Input
                                    className="mt-2"
                                    type="number"
                                    min={1}
                                    value={item.quantity}
                                    onChange={(e) => {
                                        const items = [...form.data.items];
                                        items[index] = {
                                            ...items[index],
                                            quantity: Number(e.target.value),
                                        };
                                        form.setData('items', items);
                                    }}
                                />
                            </div>
                            <div>
                                <Label>Unit price</Label>
                                <Input
                                    className="mt-2"
                                    type="number"
                                    min={0}
                                    step="0.01"
                                    value={item.unit_price}
                                    onChange={(e) => {
                                        const items = [...form.data.items];
                                        items[index] = {
                                            ...items[index],
                                            unit_price: e.target.value,
                                        };
                                        form.setData('items', items);
                                    }}
                                />
                            </div>
                            <div className="flex items-end">
                                <Button
                                    type="button"
                                    variant="destructive"
                                    size="sm"
                                    disabled={form.data.items.length === 1}
                                    onClick={() =>
                                        form.setData(
                                            'items',
                                            form.data.items.filter(
                                                (_, i) => i !== index,
                                            ),
                                        )
                                    }
                                >
                                    <Trash2 className="size-4" />
                                </Button>
                            </div>
                            {addonOptions.length > 0 && (
                                <div className="md:col-span-4">
                                    <Label>Addons</Label>
                                    <div className="mt-2 flex flex-wrap gap-3">
                                        {addonOptions.map((option) => {
                                            const selected = item.addons.find(
                                                (addon) =>
                                                    addon.addon_id ===
                                                    option.value,
                                            );
                                            return (
                                                <label
                                                    key={option.value}
                                                    className="flex items-center gap-2 text-sm"
                                                >
                                                    <input
                                                        type="checkbox"
                                                        checked={Boolean(
                                                            selected,
                                                        )}
                                                        onChange={(e) => {
                                                            const items = [
                                                                ...form.data
                                                                    .items,
                                                            ];
                                                            const addons = e
                                                                .target.checked
                                                                ? [
                                                                      ...items[
                                                                          index
                                                                      ].addons,
                                                                      {
                                                                          addon_id:
                                                                              option.value,
                                                                          quantity: 1,
                                                                      },
                                                                  ]
                                                                : items[
                                                                      index
                                                                  ].addons.filter(
                                                                      (
                                                                          addon,
                                                                      ) =>
                                                                          addon.addon_id !==
                                                                          option.value,
                                                                  );
                                                            items[index] = {
                                                                ...items[index],
                                                                addons,
                                                            };
                                                            form.setData(
                                                                'items',
                                                                items,
                                                            );
                                                        }}
                                                    />
                                                    <span>
                                                        {option.label} ·{' '}
                                                        {option.price_formatted}
                                                    </span>
                                                </label>
                                            );
                                        })}
                                    </div>
                                </div>
                            )}
                        </div>
                    ))}
                </div>
            </div>

            <div className="grid gap-4 rounded-xl border border-border/80 bg-muted/30 p-4 md:grid-cols-3">
                <Field
                    label="Discount amount"
                    error={form.errors.discount_amount}
                    htmlFor="discount_amount"
                >
                    <Input
                        id="discount_amount"
                        type="number"
                        min={0}
                        step="0.01"
                        value={form.data.discount_amount}
                        onChange={(e) =>
                            form.setData('discount_amount', e.target.value)
                        }
                    />
                </Field>
                <Field
                    label="Discount %"
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
                <Field
                    label="Delivery fee"
                    error={form.errors.delivery_fee}
                    htmlFor="delivery_fee"
                >
                    <Input
                        id="delivery_fee"
                        type="number"
                        min={0}
                        step="0.01"
                        value={form.data.delivery_fee}
                        onChange={(e) =>
                            form.setData('delivery_fee', e.target.value)
                        }
                    />
                </Field>
                <Field
                    label="Amount paid"
                    error={form.errors.amount_paid}
                    htmlFor="amount_paid"
                >
                    <Input
                        id="amount_paid"
                        type="number"
                        min={0}
                        step="0.01"
                        value={form.data.amount_paid}
                        onChange={(e) =>
                            form.setData('amount_paid', e.target.value)
                        }
                    />
                </Field>
                <div className="md:col-span-2">
                    <p className="text-[11px] font-bold tracking-[0.08em] text-muted-foreground uppercase">
                        Quote preview
                    </p>
                    <p className="mt-2 text-sm text-muted-foreground">
                        List {formatPkr(totals.list)} − discount{' '}
                        {formatPkr(totals.discount)} + fee{' '}
                        {formatPkr(totals.fee)}
                    </p>
                    <p className="mt-1 font-display text-2xl font-semibold">
                        {formatPkr(totals.quoted)}
                    </p>
                </div>
            </div>

            <div className="grid gap-4 md:grid-cols-2">
                <Field
                    label="Delivery person"
                    error={form.errors.delivery_person_name}
                    htmlFor="delivery_person_name"
                >
                    <Input
                        id="delivery_person_name"
                        value={form.data.delivery_person_name}
                        onChange={(e) =>
                            form.setData(
                                'delivery_person_name',
                                e.target.value,
                            )
                        }
                    />
                </Field>
                <Field label="Notes" error={form.errors.notes} htmlFor="notes">
                    <textarea
                        id="notes"
                        className={cn(selectClass, 'min-h-20 py-2')}
                        value={form.data.notes}
                        onChange={(e) => form.setData('notes', e.target.value)}
                    />
                </Field>
            </div>

            <div className="flex flex-wrap gap-2">
                <Button type="submit" disabled={form.processing}>
                    {mode === 'create' ? 'Create order' : 'Save changes'}
                </Button>
                <Button variant="outline" asChild>
                    <Link href={cancelHref}>Cancel</Link>
                </Button>
            </div>
        </form>
    );
}

function Field({
    label,
    error,
    htmlFor,
    children,
}: {
    label: string;
    error?: string;
    htmlFor: string;
    children: ReactNode;
}) {
    return (
        <div>
            <Label htmlFor={htmlFor}>{label}</Label>
            <div className="mt-2">{children}</div>
            <InputError message={error} className="mt-2" />
        </div>
    );
}

const selectClass =
    'border-input flex h-9 w-full rounded-md border bg-background px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50';
