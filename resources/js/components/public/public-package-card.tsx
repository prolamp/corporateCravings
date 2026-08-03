import { Link } from '@inertiajs/react';
import { MessageCircle } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

export type PublicPackageCardData = {
    id: number;
    title: string;
    slug: string;
    short_description: string | null;
    image_url: string | null;
    price_per_person_formatted: string;
    items_count: number;
    href: string;
};

type Props = {
    package: PublicPackageCardData;
};

export function PublicPackageCard({ package: pkg }: Props) {
    return (
        <article
            className={cn(
                'flex h-full flex-col overflow-hidden rounded-lg border border-border/80 bg-card',
                'transition-shadow hover:shadow-md',
            )}
        >
            <Link
                href={pkg.href}
                className="group relative block aspect-4/3 overflow-hidden bg-muted"
            >
                {pkg.image_url ? (
                    <img
                        src={pkg.image_url}
                        alt={pkg.title}
                        className="size-full object-cover transition-transform duration-500 group-hover:scale-105"
                    />
                ) : (
                    <div className="flex size-full items-center justify-center text-sm text-muted-foreground">
                        No image
                    </div>
                )}
                <span className="absolute top-3 left-3 rounded-full bg-[#f6c60c] px-3 py-1 text-[11px] font-bold tracking-[0.06em] text-[#695300] uppercase">
                    Corporate
                </span>
            </Link>

            <div className="flex flex-1 flex-col p-5">
                <Link href={pkg.href} className="block">
                    <h3 className="font-sans text-xl font-semibold tracking-tight text-foreground">
                        {pkg.title}
                    </h3>
                    {pkg.short_description && (
                        <p className="mt-2 line-clamp-2 text-sm leading-5 text-muted-foreground">
                            {pkg.short_description}
                        </p>
                    )}
                </Link>

                <p className="mt-3 text-xs text-muted-foreground">
                    {pkg.items_count}{' '}
                    {pkg.items_count === 1 ? 'dish included' : 'dishes included'}
                </p>

                <div className="mt-auto flex items-end justify-between gap-3 pt-5">
                    <div>
                        <p className="text-[11px] font-bold tracking-[0.08em] text-muted-foreground uppercase">
                            Per person
                        </p>
                        <p className="font-display text-2xl font-bold text-primary">
                            {pkg.price_per_person_formatted}
                        </p>
                    </div>
                    <Button
                        asChild
                        size="sm"
                        className="shrink-0 gap-1.5 bg-[#111827] text-white hover:bg-[#1b1c1a]"
                    >
                        <Link href={pkg.href}>
                            Details
                            <MessageCircle className="size-3.5 opacity-80" />
                        </Link>
                    </Button>
                </div>
            </div>
        </article>
    );
}
