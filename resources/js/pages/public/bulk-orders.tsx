import { Link, router, usePage } from '@inertiajs/react';
import { PublicBulkDishCard } from '@/components/public/public-bulk-dish-card';
import { SeoHead } from '@/components/public/seo-head';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

type CategoryChip = {
    id: number;
    name: string;
    slug: string;
    dishes_count: number;
};

type BulkDish = {
    id: number;
    title: string;
    slug: string;
    short_description: string | null;
    category_name: string | null;
    image_url: string | null;
    starting_price_formatted: string | null;
    tiers: Array<{
        id: number;
        label: string;
        quantity: number;
        unit: string;
        price: number;
        price_formatted: string;
    }>;
    tiers_count: number;
    href: string;
};

type Props = {
    filters: { category: string | null };
    categories: CategoryChip[];
    dishes: BulkDish[];
    dishes_count: number;
};

export default function PublicBulkOrders({
    filters,
    categories,
    dishes,
    dishes_count,
}: Props) {
    const { site } = usePage().props;
    const whatsappUrl = site?.whatsapp_url;

    function selectCategory(slug: string | null) {
        router.get(
            '/bulk-orders',
            slug ? { category: slug } : {},
            { preserveState: true, replace: true, preserveScroll: true },
        );
    }

    return (
        <>
            <SeoHead
                title="Bulk Orders"
                description="Bulk and kg catering for Islamabad events—deghs, trays, and large orders quoted on WhatsApp."
                path="/bulk-orders"
            />

            <div className="mx-auto max-w-7xl px-4 pt-10 pb-4 md:px-10 md:pt-12">
                <header className="mb-10 md:mb-12">
                    <h1 className="font-display text-4xl font-bold tracking-tight text-foreground md:text-5xl">
                        Bulk Orders
                    </h1>
                    <p className="mt-4 max-w-2xl text-base text-muted-foreground md:text-lg">
                        Large trays and deghs by weight for celebrations,
                        offices, and gatherings. Choose a kg tier, then quote on
                        WhatsApp — not available in the online cart.
                    </p>
                </header>
            </div>

            <section className="pb-16 md:pb-20">
                <div className="mx-auto max-w-7xl px-4 md:px-10">
                    <div className="-mx-4 flex gap-2 overflow-x-auto px-4 pb-2 md:mx-0 md:flex-wrap md:overflow-visible md:px-0">
                        <Chip
                            active={!filters.category}
                            onClick={() => selectCategory(null)}
                            label="All bulk dishes"
                        />
                        {categories.map((category) => (
                            <Chip
                                key={category.id}
                                active={filters.category === category.slug}
                                onClick={() => selectCategory(category.slug)}
                                label={category.name}
                            />
                        ))}
                    </div>

                    <div className="mt-6 flex flex-wrap items-end justify-between gap-4">
                        <p className="text-sm text-muted-foreground">
                            {dishes_count}{' '}
                            {dishes_count === 1 ? 'dish' : 'dishes'}
                            {filters.category
                                ? ` in ${categories.find((c) => c.slug === filters.category)?.name ?? 'this category'}`
                                : ''}
                        </p>
                        {whatsappUrl && (
                            <Button
                                asChild
                                variant="outline"
                                className="border-[#25D366]/70 text-[#128C7E] hover:bg-[#25D366]/10"
                            >
                                <a
                                    href={whatsappUrl}
                                    target="_blank"
                                    rel="noreferrer"
                                >
                                    Custom tray size? WhatsApp us
                                </a>
                            </Button>
                        )}
                    </div>

                    {dishes.length === 0 ? (
                        <p className="mt-10 rounded-lg border border-border/80 px-4 py-12 text-center text-sm text-muted-foreground">
                            No bulk kg dishes are available
                            {filters.category ? ' in this category' : ''} yet.
                        </p>
                    ) : (
                        <div className="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            {dishes.map((dish) => (
                                <PublicBulkDishCard key={dish.id} dish={dish} />
                            ))}
                        </div>
                    )}

                    <p className="mt-12 text-center text-sm text-muted-foreground">
                        Need single servings?{' '}
                        <Link
                            href="/menu"
                            className="font-medium text-primary underline-offset-4 hover:underline"
                        >
                            Browse the menu
                        </Link>
                        {' · '}
                        Office packages?{' '}
                        <Link
                            href="/packages"
                            className="font-medium text-primary underline-offset-4 hover:underline"
                        >
                            Corporate packages
                        </Link>
                    </p>
                </div>
            </section>
        </>
    );
}

function Chip({
    label,
    active,
    onClick,
}: {
    label: string;
    active: boolean;
    onClick: () => void;
}) {
    return (
        <button
            type="button"
            onClick={onClick}
            className={cn(
                'shrink-0 cursor-pointer rounded-full border px-4 py-2 text-sm font-medium transition-colors',
                active
                    ? 'border-primary bg-primary text-primary-foreground'
                    : 'border-border bg-card text-foreground hover:border-primary/40',
            )}
        >
            {label}
        </button>
    );
}
