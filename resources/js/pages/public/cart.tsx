import { Link, router, usePage } from '@inertiajs/react';
import { SeoHead } from '@/components/public/seo-head';
import { Minus, Plus, Trash2 } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';
import { Button } from '@/components/ui/button';

type CartAddon = {
    id: number;
    title: string;
    price_formatted: string;
};

type CartItem = {
    key: string;
    dish_title: string;
    dish_href: string;
    image_url: string | null;
    variation_label: string;
    unit_price_formatted: string;
    unit_total: number;
    addons: CartAddon[];
    quantity: number;
    line_total: number;
    line_total_formatted: string;
};

type Props = {
    items: CartItem[];
    items_count: number;
    quantity: number;
    subtotal: number;
    subtotal_formatted: string;
    empty: boolean;
};

function formatRs(amount: number): string {
    return `Rs. ${Math.round(amount).toLocaleString('en-PK')}`;
}

const visitOptions = {
    preserveScroll: true,
    preserveState: false,
    replace: true,
} as const;

export default function PublicCart({
    items: serverItems,
    subtotal_formatted: serverSubtotalFormatted,
    empty: serverEmpty,
}: Props) {
    const { site } = usePage().props;
    const whatsappUrl = site?.whatsapp_url;

    // Optimistic local copy — Inertia can skip the first swap after a full
    // page load, so +/- must update the UI immediately.
    const [items, setItems] = useState(serverItems);
    const [pending, setPending] = useState(false);

    useEffect(() => {
        setItems(serverItems);
    }, [serverItems]);

    const quantity = useMemo(
        () => items.reduce((sum, item) => sum + item.quantity, 0),
        [items],
    );
    const subtotal = useMemo(
        () => items.reduce((sum, item) => sum + item.line_total, 0),
        [items],
    );
    const empty = items.length === 0;
    const subtotalFormatted = empty
        ? serverEmpty
            ? serverSubtotalFormatted
            : formatRs(0)
        : formatRs(subtotal);

    function applyQuantity(key: string, next: number) {
        setItems((current) => {
            if (next < 1) {
                return current.filter((item) => item.key !== key);
            }

            return current.map((item) => {
                if (item.key !== key) {
                    return item;
                }

                const lineTotal = item.unit_total * next;

                return {
                    ...item,
                    quantity: next,
                    line_total: lineTotal,
                    line_total_formatted: formatRs(lineTotal),
                };
            });
        });
    }

    function setQuantity(key: string, next: number) {
        if (pending) {
            return;
        }

        applyQuantity(key, next);
        setPending(true);

        router.patch(
            '/cart/items',
            { key, quantity: next },
            {
                ...visitOptions,
                onFinish: () => setPending(false),
                onError: () => router.reload({ preserveScroll: true }),
            },
        );
    }

    function removeItem(key: string) {
        if (pending) {
            return;
        }

        applyQuantity(key, 0);
        setPending(true);

        router.delete('/cart/items', {
            ...visitOptions,
            data: { key },
            onFinish: () => setPending(false),
            onError: () => router.reload({ preserveScroll: true }),
        });
    }

    function clearCart() {
        if (pending) {
            return;
        }

        setItems([]);
        setPending(true);

        router.delete('/cart', {
            ...visitOptions,
            onFinish: () => setPending(false),
            onError: () => router.reload({ preserveScroll: true }),
        });
    }

    return (
        <>
            <SeoHead title="Your Cart" path="/cart" noindex />

            <div className="mx-auto max-w-7xl px-4 py-10 pb-28 md:px-10 md:py-12 md:pb-12">
                <header className="mb-8 md:mb-10">
                    <h1 className="font-display text-4xl font-bold tracking-tight md:text-5xl">
                        Review Your Cart
                    </h1>
                    <p className="mt-3 text-muted-foreground">
                        {empty
                            ? 'Your cart is empty.'
                            : `${quantity} ${quantity === 1 ? 'item' : 'items'} selected · COD checkout next`}
                    </p>
                </header>

                {empty ? (
                    <div className="rounded-2xl border border-border/80 px-6 py-16 text-center">
                        <p className="text-muted-foreground">
                            Add single-serving dishes from the menu to get
                            started.
                        </p>
                        <Button asChild className="mt-6">
                            <Link href="/menu">Browse menu</Link>
                        </Button>
                    </div>
                ) : (
                    <div className="grid gap-10 lg:grid-cols-12 lg:gap-12">
                        <div className="space-y-4 lg:col-span-7">
                            <div className="flex items-center justify-between gap-4">
                                <p className="text-sm text-muted-foreground">
                                    {items.length}{' '}
                                    {items.length === 1 ? 'line' : 'lines'}
                                </p>
                                <button
                                    type="button"
                                    onClick={clearCart}
                                    className="cursor-pointer text-sm font-medium text-muted-foreground underline-offset-4 hover:text-destructive hover:underline"
                                >
                                    Clear cart
                                </button>
                            </div>

                            <ul className="space-y-4">
                                {items.map((item) => (
                                    <li
                                        key={item.key}
                                        className="flex flex-col gap-4 rounded-2xl border border-border/80 bg-card p-4 sm:flex-row sm:items-start sm:p-5"
                                    >
                                        <Link
                                            href={item.dish_href}
                                            className="block size-24 shrink-0 overflow-hidden rounded-xl bg-muted sm:size-28"
                                        >
                                            {item.image_url ? (
                                                <img
                                                    src={item.image_url}
                                                    alt={item.dish_title}
                                                    className="size-full object-cover"
                                                />
                                            ) : (
                                                <div className="flex size-full items-center justify-center text-xs text-muted-foreground">
                                                    No image
                                                </div>
                                            )}
                                        </Link>

                                        <div className="min-w-0 flex-1">
                                            <div className="flex items-start justify-between gap-3">
                                                <div>
                                                    <Link
                                                        href={item.dish_href}
                                                        className="font-semibold tracking-tight hover:text-primary"
                                                    >
                                                        {item.dish_title}
                                                    </Link>
                                                    <p className="mt-1 text-sm text-muted-foreground">
                                                        {item.variation_label} ·{' '}
                                                        {item.unit_price_formatted}
                                                    </p>
                                                </div>
                                                <p className="font-display text-lg font-bold text-primary">
                                                    {item.line_total_formatted}
                                                </p>
                                            </div>

                                            {item.addons.length > 0 && (
                                                <ul className="mt-2 space-y-0.5 text-sm text-muted-foreground">
                                                    {item.addons.map((addon) => (
                                                        <li key={addon.id}>
                                                            + {addon.title} (
                                                            {addon.price_formatted})
                                                        </li>
                                                    ))}
                                                </ul>
                                            )}

                                            <div className="mt-4 flex flex-wrap items-center gap-3">
                                                <div className="inline-flex items-center overflow-hidden rounded-xl border border-border">
                                                    <button
                                                        type="button"
                                                        className="flex size-10 cursor-pointer items-center justify-center hover:bg-muted disabled:opacity-50"
                                                        disabled={pending}
                                                        onClick={() =>
                                                            setQuantity(
                                                                item.key,
                                                                item.quantity - 1,
                                                            )
                                                        }
                                                        aria-label="Decrease quantity"
                                                    >
                                                        <Minus className="size-4" />
                                                    </button>
                                                    <span className="w-10 text-center text-sm font-semibold tabular-nums">
                                                        {item.quantity}
                                                    </span>
                                                    <button
                                                        type="button"
                                                        className="flex size-10 cursor-pointer items-center justify-center hover:bg-muted disabled:opacity-50"
                                                        disabled={pending}
                                                        onClick={() =>
                                                            setQuantity(
                                                                item.key,
                                                                Math.min(
                                                                    99,
                                                                    item.quantity + 1,
                                                                ),
                                                            )
                                                        }
                                                        aria-label="Increase quantity"
                                                    >
                                                        <Plus className="size-4" />
                                                    </button>
                                                </div>
                                                <button
                                                    type="button"
                                                    disabled={pending}
                                                    onClick={() =>
                                                        removeItem(item.key)
                                                    }
                                                    className="inline-flex cursor-pointer items-center gap-1.5 text-sm font-medium text-muted-foreground hover:text-destructive disabled:opacity-50"
                                                >
                                                    <Trash2 className="size-4" />
                                                    Remove
                                                </button>
                                            </div>
                                        </div>
                                    </li>
                                ))}
                            </ul>
                        </div>

                        <aside className="lg:col-span-5">
                            <div className="sticky top-24 rounded-2xl border border-border/80 bg-card p-6 shadow-sm md:p-8">
                                <h2 className="text-xl font-semibold tracking-tight">
                                    Order Summary
                                </h2>
                                <dl className="mt-6 space-y-3 text-sm">
                                    <div className="flex justify-between gap-4">
                                        <dt className="text-muted-foreground">
                                            Subtotal
                                        </dt>
                                        <dd className="font-medium">
                                            {subtotalFormatted}
                                        </dd>
                                    </div>
                                    <div className="flex justify-between gap-4">
                                        <dt className="text-muted-foreground">
                                            Delivery fee
                                        </dt>
                                        <dd className="text-muted-foreground">
                                            At checkout
                                        </dd>
                                    </div>
                                    <div className="flex justify-between gap-4 border-t border-border/80 pt-3 text-base">
                                        <dt className="font-semibold">Total</dt>
                                        <dd className="font-display text-2xl font-bold text-primary">
                                            {subtotalFormatted}
                                        </dd>
                                    </div>
                                </dl>
                                <p className="mt-3 text-xs text-muted-foreground">
                                    Delivery zone fee is applied on the COD
                                    checkout page.
                                </p>

                                <Button
                                    asChild
                                    size="lg"
                                    className="mt-6 min-h-12 w-full font-bold"
                                >
                                    <Link href="/checkout">
                                        Proceed to Checkout
                                    </Link>
                                </Button>

                                {whatsappUrl && (
                                    <a
                                        href={whatsappUrl}
                                        target="_blank"
                                        rel="noreferrer"
                                        className="mt-3 flex min-h-11 w-full cursor-pointer items-center justify-center rounded-md border border-[#25D366]/50 text-sm font-semibold text-[#128C7E] hover:bg-[#25D366]/10"
                                    >
                                        Order via WhatsApp
                                    </a>
                                )}

                                <Link
                                    href="/menu"
                                    className="mt-4 block text-center text-sm font-medium text-primary underline-offset-4 hover:underline"
                                >
                                    Continue shopping
                                </Link>
                            </div>
                        </aside>
                    </div>
                )}
            </div>

            {!empty && (
                <div className="fixed inset-x-0 bottom-0 z-40 border-t border-border/80 bg-background/95 p-4 backdrop-blur md:hidden">
                    <div className="mx-auto flex max-w-7xl items-center justify-between gap-4">
                        <div>
                            <p className="text-[11px] font-bold tracking-[0.08em] text-muted-foreground uppercase">
                                Total
                            </p>
                            <p className="font-display text-xl font-bold text-primary">
                                {subtotalFormatted}
                            </p>
                        </div>
                        <Button asChild className="min-h-11 px-6 font-bold">
                            <Link href="/checkout">Checkout</Link>
                        </Button>
                    </div>
                </div>
            )}
        </>
    );
}
