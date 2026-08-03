import { Link, router, usePage } from '@inertiajs/react';
import { ChevronRight, Minus, Plus } from 'lucide-react';
import { useMemo, useState } from 'react';
import { AddShoppingCartIcon } from '@/components/public/add-shopping-cart-icon';
import { SeoHead } from '@/components/public/seo-head';
import { Button } from '@/components/ui/button';
import { trackAddToCart } from '@/lib/analytics';
import {
    absoluteUrl,
    breadcrumbJsonLd,
    dishProductJsonLd,
} from '@/lib/seo-jsonld';
import { cn } from '@/lib/utils';

type Variation = {
    id: number;
    label: string;
    quantity: number;
    unit: string;
    price: number;
    price_formatted: string;
    type: string;
    cartable: boolean;
};

type AddonOption = {
    id: number;
    title: string;
    type: string;
    price: number;
    price_formatted: string;
};

type Props = {
    dish: {
        id: number;
        title: string;
        slug: string;
        short_description: string | null;
        category_name: string | null;
        category_slug: string | null;
        image_url: string | null;
        starting_price: number | null;
        starting_price_formatted: string | null;
    };
    single_variations: Variation[];
    bulk_variations: Variation[];
    addons: AddonOption[];
};

function formatRs(amount: number): string {
    return `Rs. ${Math.round(amount).toLocaleString('en-PK')}`;
}

export default function DishShow({
    dish,
    single_variations,
    bulk_variations,
    addons,
}: Props) {
    const { site, seo } = usePage().props;
    const whatsappUrl = site?.whatsapp_url;
    const dishPath = `/menu/${dish.slug}`;
    const dishUrl = absoluteUrl(seo?.app_url ?? '', dishPath);

    const [variationId, setVariationId] = useState<number | null>(
        single_variations[0]?.id ?? null,
    );
    const [qty, setQty] = useState(1);
    const [selectedAddons, setSelectedAddons] = useState<number[]>([]);
    const [bulkId, setBulkId] = useState<number | null>(
        bulk_variations[0]?.id ?? null,
    );

    const selectedVariation =
        single_variations.find((v) => v.id === variationId) ??
        single_variations[0] ??
        null;

    const selectedBulk =
        bulk_variations.find((v) => v.id === bulkId) ??
        bulk_variations[0] ??
        null;

    const lineTotal = useMemo(() => {
        if (!selectedVariation) {
            return 0;
        }
        const addonSum = addons
            .filter((a) => selectedAddons.includes(a.id))
            .reduce((sum, a) => sum + a.price, 0);

        return (selectedVariation.price + addonSum) * qty;
    }, [selectedVariation, selectedAddons, addons, qty]);

    function toggleAddon(id: number) {
        setSelectedAddons((current) =>
            current.includes(id)
                ? current.filter((value) => value !== id)
                : [...current, id],
        );
    }

    function addToCart() {
        if (!selectedVariation) {
            return;
        }

        router.post(
            '/cart/items',
            {
                dish_variation_id: selectedVariation.id,
                quantity: qty,
                addon_ids: selectedAddons,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    trackAddToCart({
                        item_id: dish.slug,
                        quantity: qty,
                        value: lineTotal,
                    });
                    setSelectedAddons([]);
                    setQty(1);
                },
            },
        );
    }

    function bulkWhatsAppHref(): string | null {
        if (!whatsappUrl || !selectedBulk) {
            return whatsappUrl;
        }

        const message = encodeURIComponent(
            `Hi Corporate Cravings — I'd like a quote for ${dish.title} (${selectedBulk.label}, ${selectedBulk.price_formatted}).`,
        );

        return `${whatsappUrl}?text=${message}`;
    }

    return (
        <>
            <SeoHead
                title={dish.title}
                description={
                    dish.short_description ||
                    `${dish.title} from Corporate Cravings—order single servings online or quote bulk on WhatsApp.`
                }
                image={dish.image_url}
                path={dishPath}
                type="product"
                jsonLd={[
                    dishProductJsonLd({
                        name: dish.title,
                        description: dish.short_description,
                        image: dish.image_url,
                        url: dishUrl,
                        price: dish.starting_price,
                    }),
                    breadcrumbJsonLd([
                        { name: 'Home', url: absoluteUrl(seo?.app_url ?? '', '/') },
                        {
                            name: 'Menu',
                            url: absoluteUrl(seo?.app_url ?? '', '/menu'),
                        },
                        { name: dish.title, url: dishUrl },
                    ]),
                ]}
            />

            <div className="mx-auto max-w-7xl px-4 py-8 pb-28 md:px-10 md:py-12 md:pb-12">
                <nav className="mb-8 flex flex-wrap items-center gap-1 text-[11px] font-bold tracking-[0.08em] text-muted-foreground uppercase">
                    <Link href="/menu" className="hover:text-primary">
                        Menu
                    </Link>
                    {dish.category_name && dish.category_slug && (
                        <>
                            <ChevronRight className="size-3.5" />
                            <Link
                                href={`/menu?category=${dish.category_slug}`}
                                className="hover:text-primary"
                            >
                                {dish.category_name}
                            </Link>
                        </>
                    )}
                    <ChevronRight className="size-3.5" />
                    <span className="text-primary">{dish.title}</span>
                </nav>

                <div className="grid gap-10 lg:grid-cols-12 lg:gap-12">
                    <div className="space-y-8 lg:col-span-7">
                        <div className="aspect-4/3 overflow-hidden rounded-2xl bg-muted shadow-sm">
                            {dish.image_url ? (
                                <img
                                    src={dish.image_url}
                                    alt={dish.title}
                                    className="size-full object-cover"
                                />
                            ) : (
                                <div className="flex size-full items-center justify-center text-sm text-muted-foreground">
                                    No image
                                </div>
                            )}
                        </div>

                        <div>
                            <h1 className="font-display text-3xl font-bold tracking-tight md:text-5xl">
                                {dish.title}
                            </h1>
                            {dish.short_description && (
                                <p className="mt-4 max-w-2xl text-base leading-relaxed text-muted-foreground md:text-lg">
                                    {dish.short_description}
                                </p>
                            )}
                        </div>
                    </div>

                    <div className="space-y-6 lg:col-span-5">
                        {single_variations.length > 0 && (
                            <section className="rounded-2xl border border-border/80 bg-card p-6 shadow-sm md:p-8">
                                <div className="mb-6 flex items-start justify-between gap-4">
                                    <h2 className="text-xl font-semibold tracking-tight">
                                        Single serving
                                    </h2>
                                    <div className="text-right">
                                        <p className="text-[11px] font-bold tracking-[0.08em] text-muted-foreground uppercase">
                                            Starting from
                                        </p>
                                        <p className="font-display text-2xl font-bold text-primary">
                                            {dish.starting_price_formatted ??
                                                '—'}
                                        </p>
                                    </div>
                                </div>

                                <div className="space-y-3">
                                    <p className="text-[11px] font-bold tracking-[0.08em] uppercase">
                                        Select variation
                                    </p>
                                    <div className="grid gap-3 sm:grid-cols-2">
                                        {single_variations.map((variation) => {
                                            const active =
                                                variation.id ===
                                                selectedVariation?.id;

                                            return (
                                                <button
                                                    key={variation.id}
                                                    type="button"
                                                    onClick={() =>
                                                        setVariationId(
                                                            variation.id,
                                                        )
                                                    }
                                                    className={cn(
                                                        'cursor-pointer rounded-xl border px-4 py-3 text-left transition-colors',
                                                        active
                                                            ? 'border-primary bg-[#f6c60c]/15'
                                                            : 'border-border hover:border-primary/50',
                                                    )}
                                                >
                                                    <p className="text-sm font-bold">
                                                        {variation.label}
                                                    </p>
                                                    <p className="mt-0.5 text-sm text-muted-foreground">
                                                        {variation.price_formatted}
                                                    </p>
                                                </button>
                                            );
                                        })}
                                    </div>
                                </div>

                                {addons.length > 0 && (
                                    <div className="mt-6 space-y-3">
                                        <p className="text-[11px] font-bold tracking-[0.08em] uppercase">
                                            Addons
                                        </p>
                                        <div className="space-y-2">
                                            {addons.map((addon) => {
                                                const checked =
                                                    selectedAddons.includes(
                                                        addon.id,
                                                    );

                                                return (
                                                    <label
                                                        key={addon.id}
                                                        className={cn(
                                                            'flex cursor-pointer items-center justify-between gap-3 rounded-xl border px-4 py-3',
                                                            checked
                                                                ? 'border-primary bg-[#f6c60c]/10'
                                                                : 'border-border',
                                                        )}
                                                    >
                                                        <span className="flex items-center gap-3 text-sm">
                                                            <input
                                                                type="checkbox"
                                                                checked={checked}
                                                                onChange={() =>
                                                                    toggleAddon(
                                                                        addon.id,
                                                                    )
                                                                }
                                                                className="size-4 accent-primary"
                                                            />
                                                            {addon.title}
                                                        </span>
                                                        <span className="text-sm font-medium text-muted-foreground">
                                                            +
                                                            {
                                                                addon.price_formatted
                                                            }
                                                        </span>
                                                    </label>
                                                );
                                            })}
                                        </div>
                                    </div>
                                )}

                                <div className="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center">
                                    <div className="flex h-14 items-center rounded-xl border border-border">
                                        <button
                                            type="button"
                                            className="flex size-12 cursor-pointer items-center justify-center hover:bg-muted"
                                            onClick={() =>
                                                setQty((n) => Math.max(1, n - 1))
                                            }
                                            aria-label="Decrease quantity"
                                        >
                                            <Minus className="size-4" />
                                        </button>
                                        <span className="w-12 text-center text-lg font-semibold tabular-nums">
                                            {qty}
                                        </span>
                                        <button
                                            type="button"
                                            className="flex size-12 cursor-pointer items-center justify-center hover:bg-muted"
                                            onClick={() =>
                                                setQty((n) => Math.min(99, n + 1))
                                            }
                                            aria-label="Increase quantity"
                                        >
                                            <Plus className="size-4" />
                                        </button>
                                    </div>
                                    <Button
                                        type="button"
                                        className="h-14 flex-1 gap-2 text-base font-bold"
                                        onClick={addToCart}
                                    >
                                        <AddShoppingCartIcon className="size-5" />
                                        Add to cart · {formatRs(lineTotal)}
                                    </Button>
                                </div>
                            </section>
                        )}

                        {bulk_variations.length > 0 && (
                            <section className="rounded-2xl border border-border/80 bg-muted/40 p-6 md:p-8">
                                <div className="mb-6">
                                    <h2 className="text-xl font-semibold tracking-tight">
                                        Bulk offerings
                                    </h2>
                                    <p className="mt-1 text-[11px] font-bold tracking-[0.08em] text-muted-foreground uppercase">
                                        Corporate & events · WhatsApp only
                                    </p>
                                </div>

                                <div className="space-y-3">
                                    {bulk_variations.map((variation) => {
                                        const active =
                                            variation.id === selectedBulk?.id;

                                        return (
                                            <button
                                                key={variation.id}
                                                type="button"
                                                onClick={() =>
                                                    setBulkId(variation.id)
                                                }
                                                className={cn(
                                                    'flex w-full cursor-pointer items-center justify-between gap-4 rounded-xl border bg-card px-4 py-4 text-left transition-colors',
                                                    active
                                                        ? 'border-primary ring-1 ring-primary'
                                                        : 'border-border hover:border-primary/40',
                                                )}
                                            >
                                                <div>
                                                    <p className="text-sm font-bold uppercase">
                                                        {variation.label}
                                                    </p>
                                                    <p className="text-xs text-muted-foreground">
                                                        {variation.quantity}{' '}
                                                        {variation.unit}
                                                    </p>
                                                </div>
                                                <div className="text-right">
                                                    <p className="font-semibold">
                                                        {
                                                            variation.price_formatted
                                                        }
                                                    </p>
                                                    <p className="text-[10px] tracking-wide text-muted-foreground uppercase">
                                                        Estimated price
                                                    </p>
                                                </div>
                                            </button>
                                        );
                                    })}
                                </div>

                                {bulkWhatsAppHref() ? (
                                    <a
                                        href={bulkWhatsAppHref()!}
                                        target="_blank"
                                        rel="noreferrer"
                                        data-wa-placement="dish_bulk_quote"
                                        className="mt-6 flex h-14 w-full cursor-pointer items-center justify-center gap-2 rounded-xl bg-[#25D366] text-base font-bold text-white hover:opacity-90"
                                    >
                                        Quote on WhatsApp
                                    </a>
                                ) : (
                                    <p className="mt-6 text-center text-sm text-muted-foreground">
                                        WhatsApp number is not configured yet.
                                    </p>
                                )}
                                <p className="mt-3 text-center text-[11px] text-muted-foreground">
                                    Bulk orders need advance notice for
                                    guaranteed quality.
                                </p>
                            </section>
                        )}

                        {single_variations.length === 0 &&
                            bulk_variations.length === 0 && (
                                <p className="rounded-xl border border-border/80 px-4 py-8 text-center text-sm text-muted-foreground">
                                    This dish has no active serving options
                                    right now.
                                </p>
                            )}
                    </div>
                </div>
            </div>

            {single_variations.length > 0 && (
                <div className="fixed inset-x-0 bottom-0 z-40 border-t border-border bg-background p-4 md:hidden">
                    <div className="flex items-center gap-3">
                        <div>
                            <p className="text-[10px] font-bold tracking-wide text-muted-foreground uppercase">
                                Total
                            </p>
                            <p className="font-display text-lg font-bold text-primary">
                                {formatRs(lineTotal)}
                            </p>
                        </div>
                        <Button
                            type="button"
                            className="h-12 flex-1 font-bold"
                            onClick={addToCart}
                        >
                            Add to cart
                        </Button>
                    </div>
                </div>
            )}
        </>
    );
}
