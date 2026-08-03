import { Link, router } from '@inertiajs/react';
import { PublicDishCard } from '@/components/public/public-dish-card';
import { SeoHead } from '@/components/public/seo-head';
import { cn } from '@/lib/utils';

type CategoryChip = {
    id: number;
    name: string;
    slug: string;
    dishes_count: number;
};

type Dish = {
    id: number;
    title: string;
    slug: string;
    short_description: string | null;
    category_name: string | null;
    image_url: string | null;
    starting_price_formatted: string | null;
    href: string;
};

type Props = {
    filters: { category: string | null };
    categories: CategoryChip[];
    dishes: Dish[];
    dishes_count: number;
};

export default function PublicMenu({
    filters,
    categories,
    dishes,
    dishes_count,
}: Props) {
    function selectCategory(slug: string | null) {
        router.get(
            '/menu',
            slug ? { category: slug } : {},
            { preserveState: true, replace: true, preserveScroll: true },
        );
    }

    return (
        <>
            <SeoHead
                title="Menu"
                description="Browse Corporate Cravings’s public menu—authentic Pakistani dishes for office lunches and events in Islamabad."
                path="/menu"
            />

            <div className="mx-auto max-w-7xl px-4 pt-10 pb-4 md:px-10 md:pt-12">
                <header className="mb-10 md:mb-12">
                    <h1 className="font-display text-4xl font-bold tracking-tight text-foreground md:text-5xl">
                        Our Public Menu
                    </h1>
                    <p className="mt-4 max-w-2xl text-base text-muted-foreground md:text-lg">
                        Discover authentic flavors of Pakistan, meticulously
                        prepared for professional settings. From aromatic
                        Biryanis to succulent Karahis.
                    </p>
                </header>
            </div>

            <section className="pb-12 md:pb-16">
                <div className="mx-auto max-w-7xl px-4 md:px-10">
                    <div className="-mx-4 flex gap-2 overflow-x-auto px-4 pb-2 md:mx-0 md:flex-wrap md:overflow-visible md:px-0">
                        <Chip
                            active={!filters.category}
                            onClick={() => selectCategory(null)}
                            label="All Dishes"
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

                    <div className="mt-6 flex items-end justify-between gap-4">
                        <p className="text-sm text-muted-foreground">
                            {dishes_count}{' '}
                            {dishes_count === 1 ? 'dish' : 'dishes'}
                            {filters.category
                                ? ` in ${categories.find((c) => c.slug === filters.category)?.name ?? 'this category'}`
                                : ''}
                        </p>
                        <Link
                            href="/bulk-orders"
                            className="hidden text-sm font-medium text-primary underline-offset-4 hover:underline sm:inline"
                        >
                            Looking for bulk kg? View bulk orders
                        </Link>
                    </div>

                    {dishes.length === 0 ? (
                        <p className="mt-10 rounded-lg border border-border/80 px-4 py-12 text-center text-sm text-muted-foreground">
                            No single-serving dishes are available
                            {filters.category ? ' in this category' : ''} yet.
                        </p>
                    ) : (
                        <div className="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                            {dishes.map((dish) => (
                                <PublicDishCard
                                    key={dish.id}
                                    dish={dish}
                                    variant="menu"
                                />
                            ))}
                        </div>
                    )}
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
