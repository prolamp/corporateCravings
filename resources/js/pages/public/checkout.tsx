import { Link, useForm, usePage } from '@inertiajs/react';
import {
    Children,
    cloneElement,
    isValidElement,
    useEffect,
    type FormEvent,
    type ReactElement,
    type ReactNode,
} from 'react';
import InputError from '@/components/input-error';
import { SeoHead } from '@/components/public/seo-head';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { trackBeginCheckout } from '@/lib/analytics';
import { cn } from '@/lib/utils';

type CartItem = {
    key: string;
    dish_title: string;
    variation_label: string;
    quantity: number;
    line_total_formatted: string;
    addons: Array<{ id: number; title: string }>;
};

type Zone = {
    id: number;
    name: string;
    fee: number;
    fee_formatted: string;
};

type Props = {
    items: CartItem[];
    items_count: number;
    quantity: number;
    subtotal: number;
    subtotal_formatted: string;
    zones: Zone[];
    payment_method: string;
    customer: {
        name: string;
        phone: string;
        email: string;
        company_name: string;
        delivery_address: string;
    };
};

function formatRs(amount: number): string {
    return `Rs. ${Math.round(amount).toLocaleString('en-PK')}`;
}

export default function PublicCheckout({
    items,
    subtotal,
    subtotal_formatted,
    zones,
    customer,
}: Props) {
    const { site } = usePage().props;
    const whatsappUrl = site?.whatsapp_url;

    useEffect(() => {
        trackBeginCheckout({
            items_count: items.length,
            value: subtotal,
        });
    }, [items.length, subtotal]);

    const form = useForm({
        customer_name: customer.name,
        customer_phone: customer.phone,
        customer_email: customer.email,
        company_name: customer.company_name,
        delivery_address: customer.delivery_address,
        delivery_zone_id: '',
        notes: '',
    });

    const selectedZone = zones.find(
        (zone) => String(zone.id) === String(form.data.delivery_zone_id),
    );
    const deliveryFee = selectedZone?.fee ?? 0;
    const total = subtotal + deliveryFee;

    function validate(): Record<string, string> {
        const errors: Record<string, string> = {};
        const name = form.data.customer_name.trim();
        const phone = form.data.customer_phone.trim();
        const address = form.data.delivery_address.trim();
        const zoneId = String(form.data.delivery_zone_id || '').trim();
        const email = form.data.customer_email.trim();

        if (!name) {
            errors.customer_name = 'Full name is required.';
        }

        if (!phone) {
            errors.customer_phone = 'Phone number is required.';
        } else if (!/^[0-9+\-]{8,20}$/.test(phone)) {
            errors.customer_phone = 'Enter a valid phone number.';
        }

        if (!email) {
            errors.customer_email = 'Email is required.';
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            errors.customer_email = 'Enter a valid email address.';
        }

        if (!address) {
            errors.delivery_address = 'Street address is required.';
        }

        if (!zoneId) {
            errors.delivery_zone_id = 'Please select a delivery zone.';
        }

        return errors;
    }

    function submit(event: FormEvent) {
        event.preventDefault();
        form.clearErrors();

        const errors = validate();
        if (Object.keys(errors).length > 0) {
            form.setError(errors);
            const firstKey = Object.keys(errors)[0];
            document
                .getElementById(`checkout-${firstKey}`)
                ?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            document.getElementById(`checkout-${firstKey}`)?.focus();

            return;
        }

        form.post('/checkout', {
            preserveScroll: true,
        });
    }

    function updateField<K extends keyof typeof form.data>(
        key: K,
        value: (typeof form.data)[K],
    ) {
        form.setData(key, value);
        if (form.errors[key as string]) {
            form.clearErrors(key as string);
        }
    }

    return (
        <>
            <SeoHead title="Checkout" path="/checkout" noindex />

            <div className="mx-auto max-w-7xl px-4 py-10 md:px-10 md:py-12">
                <nav className="mb-6 flex flex-wrap items-center gap-2 text-[11px] font-bold tracking-[0.08em] text-muted-foreground uppercase">
                    <Link href="/cart" className="hover:text-primary">
                        Cart
                    </Link>
                    <span>/</span>
                    <span className="text-primary">Checkout</span>
                </nav>

                <header className="mb-8 md:mb-10">
                    <h1 className="font-display text-4xl font-bold tracking-tight md:text-5xl">
                        Secure Checkout
                    </h1>
                    <p className="mt-3 text-muted-foreground">
                        Cash on delivery · delivery fee by zone. Your email is
                        your account — existing emails keep your history; new
                        emails create a customer login (password = phone).
                    </p>
                </header>

                {zones.length === 0 ? (
                    <div className="rounded-2xl border border-border/80 px-6 py-12 text-center">
                        <p className="text-muted-foreground">
                            Delivery zones are not configured yet. Please
                            WhatsApp us to complete your order.
                        </p>
                        {whatsappUrl && (
                            <Button asChild className="mt-6">
                                <a
                                    href={whatsappUrl}
                                    target="_blank"
                                    rel="noreferrer"
                                >
                                    WhatsApp us
                                </a>
                            </Button>
                        )}
                        <Link
                            href="/cart"
                            className="mt-4 block text-sm font-medium text-primary underline-offset-4 hover:underline"
                        >
                            Back to cart
                        </Link>
                    </div>
                ) : (
                    <form
                        noValidate
                        onSubmit={submit}
                        className="grid gap-10 lg:grid-cols-12 lg:gap-12"
                    >
                        <div className="space-y-8 lg:col-span-7">
                            <section className="rounded-2xl border border-border/80 bg-card p-6 md:p-8">
                                <h2 className="text-xl font-semibold tracking-tight">
                                    Delivery details
                                </h2>
                                <div className="mt-6 grid gap-4 sm:grid-cols-2">
                                    <Field
                                        id="checkout-customer_name"
                                        label="Full name"
                                        error={form.errors.customer_name}
                                    >
                                        <Input
                                            value={form.data.customer_name}
                                            onChange={(e) =>
                                                updateField(
                                                    'customer_name',
                                                    e.target.value,
                                                )
                                            }
                                            autoComplete="name"
                                        />
                                    </Field>
                                    <Field
                                        id="checkout-customer_phone"
                                        label="Phone number"
                                        error={form.errors.customer_phone}
                                    >
                                        <Input
                                            value={form.data.customer_phone}
                                            onChange={(e) =>
                                                updateField(
                                                    'customer_phone',
                                                    e.target.value,
                                                )
                                            }
                                            autoComplete="tel"
                                            placeholder="03XXXXXXXXX"
                                        />
                                    </Field>
                                    <Field
                                        id="checkout-customer_email"
                                        label="Email"
                                        error={form.errors.customer_email}
                                    >
                                        <Input
                                            type="email"
                                            value={form.data.customer_email}
                                            onChange={(e) =>
                                                updateField(
                                                    'customer_email',
                                                    e.target.value,
                                                )
                                            }
                                            autoComplete="email"
                                            placeholder="you@company.com"
                                        />
                                    </Field>
                                    <Field
                                        id="checkout-company_name"
                                        label="Company (optional)"
                                        error={form.errors.company_name}
                                    >
                                        <Input
                                            value={form.data.company_name}
                                            onChange={(e) =>
                                                updateField(
                                                    'company_name',
                                                    e.target.value,
                                                )
                                            }
                                            autoComplete="organization"
                                        />
                                    </Field>
                                    <div className="sm:col-span-2">
                                        <Field
                                            id="checkout-delivery_address"
                                            label="Street address"
                                            error={form.errors.delivery_address}
                                        >
                                            <Input
                                                value={
                                                    form.data.delivery_address
                                                }
                                                onChange={(e) =>
                                                    updateField(
                                                        'delivery_address',
                                                        e.target.value,
                                                    )
                                                }
                                                autoComplete="street-address"
                                            />
                                        </Field>
                                    </div>
                                    <div className="sm:col-span-2">
                                        <Field
                                            id="checkout-delivery_zone_id"
                                            label="Delivery zone"
                                            error={form.errors.delivery_zone_id}
                                        >
                                            <select
                                                value={form.data.delivery_zone_id}
                                                onChange={(e) =>
                                                    updateField(
                                                        'delivery_zone_id',
                                                        e.target.value,
                                                    )
                                                }
                                                className="border-input flex h-10 w-full cursor-pointer rounded-md border bg-background px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                            >
                                                <option value="">
                                                    Select a delivery area
                                                </option>
                                                {zones.map((zone) => (
                                                    <option
                                                        key={zone.id}
                                                        value={zone.id}
                                                    >
                                                        {zone.name}
                                                        {' — '}
                                                        {zone.fee === 0
                                                            ? 'Free delivery'
                                                            : zone.fee_formatted}
                                                    </option>
                                                ))}
                                            </select>
                                        </Field>
                                    </div>
                                    <div className="sm:col-span-2">
                                        <Field
                                            id="checkout-notes"
                                            label="Notes (optional)"
                                            error={form.errors.notes}
                                        >
                                            <textarea
                                                value={form.data.notes}
                                                onChange={(e) =>
                                                    updateField(
                                                        'notes',
                                                        e.target.value,
                                                    )
                                                }
                                                rows={3}
                                                className="border-input flex w-full rounded-md border bg-background px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                                placeholder="Gate code, floor, lunch timing…"
                                            />
                                        </Field>
                                    </div>
                                </div>
                            </section>

                            <section className="rounded-2xl border border-border/80 bg-card p-6 md:p-8">
                                <h2 className="text-xl font-semibold tracking-tight">
                                    Payment method
                                </h2>
                                <div className="mt-4 rounded-xl border border-primary bg-primary/5 px-4 py-4">
                                    <p className="font-semibold">
                                        Cash on delivery (COD)
                                    </p>
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        Pay when your meal arrives. Card and
                                        bank transfer are not available online
                                        yet.
                                    </p>
                                </div>
                            </section>
                        </div>

                        <aside className="lg:col-span-5">
                            <div className="sticky top-24 rounded-2xl border border-border/80 bg-card p-6 shadow-sm md:p-8">
                                <h2 className="text-xl font-semibold tracking-tight">
                                    Order summary
                                </h2>
                                <ul className="mt-6 space-y-3 border-b border-border/80 pb-4">
                                    {items.map((item) => (
                                        <li
                                            key={item.key}
                                            className="flex items-start justify-between gap-3 text-sm"
                                        >
                                            <div>
                                                <p className="font-medium">
                                                    {item.dish_title}
                                                </p>
                                                <p className="text-muted-foreground">
                                                    {item.variation_label} ×{' '}
                                                    {item.quantity}
                                                    {item.addons.length > 0
                                                        ? ` · +${item.addons.map((a) => a.title).join(', ')}`
                                                        : ''}
                                                </p>
                                            </div>
                                            <p className="shrink-0 font-medium">
                                                {item.line_total_formatted}
                                            </p>
                                        </li>
                                    ))}
                                </ul>

                                <dl className="mt-4 space-y-3 text-sm">
                                    <div className="flex justify-between gap-4">
                                        <dt className="text-muted-foreground">
                                            Subtotal
                                        </dt>
                                        <dd className="font-medium">
                                            {subtotal_formatted}
                                        </dd>
                                    </div>
                                    <div className="flex justify-between gap-4">
                                        <dt className="text-muted-foreground">
                                            Delivery fee
                                        </dt>
                                        <dd className="font-medium">
                                            {selectedZone
                                                ? deliveryFee === 0
                                                    ? 'Free'
                                                    : formatRs(deliveryFee)
                                                : '—'}
                                        </dd>
                                    </div>
                                    <div className="flex justify-between gap-4 border-t border-border/80 pt-3 text-base">
                                        <dt className="font-semibold">Total</dt>
                                        <dd className="font-display text-2xl font-bold text-primary">
                                            {formatRs(total)}
                                        </dd>
                                    </div>
                                </dl>

                                <InputError
                                    message={form.errors.cart}
                                    className="mt-4"
                                />

                                <Button
                                    type="submit"
                                    size="lg"
                                    disabled={form.processing}
                                    className="mt-6 min-h-12 w-full font-bold"
                                >
                                    {form.processing
                                        ? 'Placing order…'
                                        : 'Place order'}
                                </Button>

                                <Link
                                    href="/cart"
                                    className="mt-4 block text-center text-sm font-medium text-primary underline-offset-4 hover:underline"
                                >
                                    Back to cart
                                </Link>
                            </div>
                        </aside>
                    </form>
                )}
            </div>
        </>
    );
}

function Field({
    id,
    label,
    error,
    children,
}: {
    id: string;
    label: string;
    error?: string;
    children: ReactNode;
}) {
    const control = Children.map(children, (child) => {
        if (!isValidElement(child)) {
            return child;
        }

        const element = child as ReactElement<{
            id?: string;
            className?: string;
            'aria-invalid'?: boolean;
        }>;

        return cloneElement(element, {
            id,
            'aria-invalid': Boolean(error),
            className: cn(
                element.props.className,
                error && 'border-destructive focus-visible:border-destructive',
            ),
        });
    });

    return (
        <div className="space-y-2">
            <Label
                htmlFor={id}
                className="text-[11px] font-bold tracking-[0.08em] text-muted-foreground uppercase"
            >
                {label}
            </Label>
            {control}
            <InputError message={error} />
        </div>
    );
}
