import { Link } from '@inertiajs/react';
import { AddShoppingCartIcon } from '@/components/public/add-shopping-cart-icon';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

export type PublicDishCardData = {
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
    dish: PublicDishCardData;
    /** Menu listing: bordered card + cart control (Stitch). */
    variant?: 'menu' | 'simple';
};

export function PublicDishCard({ dish, variant = 'simple' }: Props) {
    if (variant === 'menu') {
        return <MenuDishCard dish={dish} />;
    }

    return (
        <Link href={dish.href} className="group block">
            <div className="relative mb-4 aspect-4/5 overflow-hidden rounded-lg bg-muted">
                <DishImage dish={dish} />
            </div>
            <h3 className="font-semibold tracking-tight">{dish.title}</h3>
            {dish.short_description && (
                <p className="mt-1 line-clamp-2 text-sm text-muted-foreground">
                    {dish.short_description}
                </p>
            )}
            {dish.starting_price_formatted && (
                <p className="mt-2 font-display text-xl font-bold text-primary">
                    {dish.starting_price_formatted}
                </p>
            )}
        </Link>
    );
}

function MenuDishCard({ dish }: { dish: PublicDishCardData }) {
    return (
        <article
            className={cn(
                'flex h-full flex-col overflow-hidden rounded-lg border border-border/80 bg-card',
                'transition-shadow hover:shadow-md',
            )}
        >
            <Link href={dish.href} className="group relative block aspect-4/3 overflow-hidden bg-muted">
                <DishImage dish={dish} />
                <span className="absolute top-3 left-3 rounded-full bg-[#f6c60c] px-3 py-1 text-[11px] font-bold tracking-[0.06em] text-[#695300] uppercase">
                    Single serving
                </span>
            </Link>

            <div className="flex flex-1 flex-col p-5">
                <Link href={dish.href} className="block">
                    <h3 className="font-sans text-xl font-semibold tracking-tight text-foreground">
                        {dish.title}
                    </h3>
                    {dish.short_description && (
                        <p className="mt-2 line-clamp-2 text-sm leading-5 text-muted-foreground">
                            {dish.short_description}
                        </p>
                    )}
                </Link>

                <div className="mt-auto flex items-end justify-between gap-3 pt-5">
                    <div>
                        <p className="text-[11px] font-bold tracking-[0.08em] text-muted-foreground uppercase">
                            Starts at
                        </p>
                        <p className="font-display text-2xl font-bold text-primary">
                            {dish.starting_price_formatted ?? '—'}
                        </p>
                    </div>
                    <Button
                        asChild
                        size="icon"
                        className="size-12 shrink-0 rounded-xl bg-[#111827] text-white hover:bg-[#1b1c1a]"
                    >
                        <Link
                            href={dish.href}
                            aria-label={`Add ${dish.title} to cart`}
                            title="Choose options and add to cart"
                        >
                            <AddShoppingCartIcon className="size-6" />
                        </Link>
                    </Button>
                </div>
            </div>
        </article>
    );
}

function DishImage({ dish }: { dish: PublicDishCardData }) {
    if (!dish.image_url) {
        return (
            <div className="flex size-full items-center justify-center text-sm text-muted-foreground">
                No image
            </div>
        );
    }

    return (
        <img
            src={dish.image_url}
            alt={dish.title}
            className="size-full object-cover transition-transform duration-500 group-hover:scale-105"
        />
    );
}
