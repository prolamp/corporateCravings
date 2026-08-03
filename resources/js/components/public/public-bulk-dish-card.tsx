import { Link } from '@inertiajs/react';
import { MessageCircle } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

export type BulkTier = {
    id: number;
    label: string;
    quantity: number;
    unit: string;
    price: number;
    price_formatted: string;
};

export type PublicBulkDishCardData = {
    id: number;
    title: string;
    slug: string;
    short_description: string | null;
    category_name: string | null;
    image_url: string | null;
    starting_price_formatted: string | null;
    tiers: BulkTier[];
    tiers_count: number;
    href: string;
};

type Props = {
    dish: PublicBulkDishCardData;
};

export function PublicBulkDishCard({ dish }: Props) {
    const previewTiers = dish.tiers.slice(0, 3);

    return (
        <article
            className={cn(
                'flex h-full flex-col overflow-hidden rounded-lg border border-border/80 bg-card',
                'transition-shadow hover:shadow-md',
            )}
        >
            <Link
                href={dish.href}
                className="group relative block aspect-4/3 overflow-hidden bg-muted"
            >
                {dish.image_url ? (
                    <img
                        src={dish.image_url}
                        alt={dish.title}
                        className="size-full object-cover transition-transform duration-500 group-hover:scale-105"
                    />
                ) : (
                    <div className="flex size-full items-center justify-center text-sm text-muted-foreground">
                        No image
                    </div>
                )}
                <span className="absolute top-3 left-3 rounded-full bg-[#f6c60c] px-3 py-1 text-[11px] font-bold tracking-[0.06em] text-[#695300] uppercase">
                    Bulk · Kg
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

                {previewTiers.length > 0 && (
                    <ul className="mt-4 space-y-1.5">
                        {previewTiers.map((tier) => (
                            <li
                                key={tier.id}
                                className="flex items-center justify-between gap-3 text-sm"
                            >
                                <span className="text-muted-foreground">
                                    {tier.label}
                                </span>
                                <span className="font-medium text-foreground">
                                    {tier.price_formatted}
                                </span>
                            </li>
                        ))}
                        {dish.tiers_count > previewTiers.length && (
                            <li className="text-xs text-muted-foreground">
                                +{dish.tiers_count - previewTiers.length} more
                                tiers
                            </li>
                        )}
                    </ul>
                )}

                <div className="mt-auto flex items-end justify-between gap-3 pt-5">
                    <div>
                        <p className="text-[11px] font-bold tracking-[0.08em] text-muted-foreground uppercase">
                            From
                        </p>
                        <p className="font-display text-2xl font-bold text-primary">
                            {dish.starting_price_formatted ?? '—'}
                        </p>
                    </div>
                    <Button
                        asChild
                        size="sm"
                        className="shrink-0 gap-1.5 bg-[#111827] text-white hover:bg-[#1b1c1a]"
                    >
                        <Link href={dish.href}>
                            Quote
                            <MessageCircle className="size-3.5 opacity-80" />
                        </Link>
                    </Button>
                </div>
            </div>
        </article>
    );
}
