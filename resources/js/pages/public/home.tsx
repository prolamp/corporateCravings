import { Link, usePage } from '@inertiajs/react';
import {
    ArrowRight,
    Briefcase,
    CheckCircle2,
    Scale,
    UtensilsCrossed,
} from 'lucide-react';
import type { ReactNode } from 'react';
import { PublicDishCard } from '@/components/public/public-dish-card';
import { SeoHead } from '@/components/public/seo-head';
import { Button } from '@/components/ui/button';
import { withOrganization } from '@/lib/seo-jsonld';
import { cn } from '@/lib/utils';

type FeaturedDish = {
    id: number;
    title: string;
    slug: string;
    short_description: string | null;
    category_name: string | null;
    image_url: string | null;
    starting_price: number | null;
    starting_price_formatted: string | null;
    href: string;
};

type Props = {
    featured_dishes: FeaturedDish[];
    package_from_formatted: string | null;
    hero_image_url: string;
    trust_image_url: string;
};

export default function PublicHome({
    featured_dishes,
    package_from_formatted,
    hero_image_url,
    trust_image_url,
}: Props) {
    const { site, seo } = usePage().props;
    const brand = site?.brand?.name ?? 'Corporate Cravings';
    const whatsappUrl = site?.whatsapp_url;

    return (
        <>
            <SeoHead
                title="Home"
                description={`Fresh Pakistani meals for Islamabad offices and events from ${brand}. Daily lunches, corporate packages, and bulk orders.`}
                image={hero_image_url}
                path="/"
                jsonLd={withOrganization(seo)}
            />

            <section className="relative flex min-h-[min(870px,100svh)] items-center overflow-hidden">
                <div className="absolute inset-0">
                    <img
                        src={hero_image_url}
                        alt=""
                        className="size-full object-cover"
                    />
                    <div className="absolute inset-0 bg-linear-to-r from-black/85 via-black/45 to-black/10" />
                </div>

                <div className="relative z-10 mx-auto w-full max-w-7xl px-4 py-20 md:px-10 md:py-28">
                    <div className="max-w-2xl animate-in fade-in slide-in-from-bottom-4 duration-700">
                        <p className="font-display text-4xl font-bold tracking-tight text-[#f6c60c] sm:text-5xl md:text-6xl">
                            {brand}
                        </p>
                        <h1 className="mt-4 font-display text-3xl font-bold tracking-tight text-white sm:text-4xl md:text-[2.75rem] md:leading-tight">
                            Fresh meals for Islamabad offices and events
                        </h1>
                        <p className="mt-4 max-w-xl text-base leading-relaxed text-white/90 sm:text-lg">
                            Daily office lunches, corporate packages, and bulk
                            orders—authentic Pakistani food, delivered on time.
                        </p>
                        <div className="mt-8 flex flex-col gap-3 sm:flex-row sm:gap-4">
                            <Button
                                asChild
                                size="lg"
                                className="min-h-12 bg-[#f6c60c] px-8 font-bold text-[#695300] hover:bg-[#ffe089]"
                            >
                                <Link href="/menu">
                                    Browse Menu
                                    <UtensilsCrossed className="size-4" />
                                </Link>
                            </Button>
                            {whatsappUrl && (
                                <Button
                                    asChild
                                    size="lg"
                                    className="min-h-12 border-0 bg-linear-to-br from-[#25D366] to-[#128C7E] px-8 font-bold text-white hover:opacity-90"
                                >
                                    <a
                                        href={whatsappUrl}
                                        target="_blank"
                                        rel="noreferrer"
                                    >
                                        WhatsApp Us
                                    </a>
                                </Button>
                            )}
                        </div>
                    </div>
                </div>
            </section>

            <section className="bg-[#FDFBF7] py-16 md:py-20 dark:bg-muted/20">
                <div className="mx-auto grid max-w-7xl gap-6 px-4 md:grid-cols-3 md:px-10">
                    <PathCard
                        icon={<UtensilsCrossed className="size-6" />}
                        title="Daily Menu"
                        body="Individual servings for small teams and daily office lunches. Order online with COD."
                        eyebrow="Single servings"
                        cta="View menu"
                        href="/menu"
                        iconClass="bg-secondary text-secondary-foreground"
                    />
                    <PathCard
                        icon={<Briefcase className="size-6" />}
                        title="Corporate Packages"
                        body="Full-course buffet solutions for seminars, meetings, and events. Quote on WhatsApp."
                        eyebrow={
                            package_from_formatted
                                ? `From ${package_from_formatted} / person`
                                : 'Per-person packages'
                        }
                        cta="View packages"
                        href="/packages"
                        iconClass="bg-[#f6c60c] text-[#695300]"
                        featured
                    />
                    <PathCard
                        icon={<Scale className="size-6" />}
                        title="Bulk Orders"
                        body="Traditional platters and deghs by weight for celebrations and large gatherings."
                        eyebrow="Kg tiers available"
                        cta="View bulk pricing"
                        href="/bulk-orders"
                        iconClass="bg-secondary text-secondary-foreground"
                    />
                </div>
            </section>

            <section className="py-16 md:py-24">
                <div className="mx-auto max-w-7xl px-4 md:px-10">
                    <div className="mb-10 flex flex-col gap-4 md:mb-12 md:flex-row md:items-end md:justify-between">
                        <div className="max-w-xl">
                            <h2 className="font-display text-3xl font-bold tracking-tight md:text-4xl">
                                Signature flavors
                            </h2>
                            <p className="mt-3 text-muted-foreground md:text-lg">
                                Our most requested dishes, crafted daily with
                                fresh ingredients and heritage spices.
                            </p>
                        </div>
                        <Link
                            href="/menu"
                            className="text-sm font-bold text-primary underline-offset-4 hover:underline"
                        >
                            Explore full menu
                        </Link>
                    </div>

                    {featured_dishes.length === 0 ? (
                        <p className="rounded-lg border border-border/80 px-4 py-10 text-center text-sm text-muted-foreground">
                            Menu dishes will appear here once the catalog is
                            published.
                        </p>
                    ) : (
                        <div className="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                            {featured_dishes.map((dish) => (
                                <PublicDishCard key={dish.id} dish={dish} />
                            ))}
                        </div>
                    )}
                </div>
            </section>

            <section className="border-y border-border/80 bg-muted/30 py-16 md:py-24">
                <div className="mx-auto grid max-w-7xl items-center gap-12 px-4 md:px-10 lg:grid-cols-2 lg:gap-16">
                    <div className="relative">
                        <div className="overflow-hidden rounded-lg shadow-lg">
                            <img
                                src={trust_image_url}
                                alt="Corporate Cravings kitchen preparing office and event meal trays"
                                className="aspect-video w-full object-cover"
                            />
                        </div>
                        <div className="absolute -right-4 -bottom-5 hidden rounded-lg bg-primary px-6 py-5 text-primary-foreground shadow-md md:block md:-right-6 md:-bottom-6 md:px-8 md:py-7">
                            <p className="font-display text-4xl leading-none font-bold md:text-5xl">
                                15+
                            </p>
                            <p className="mt-1 text-[11px] font-bold tracking-[0.08em] text-primary-foreground/85 uppercase">
                                Corporate clients
                            </p>
                        </div>
                    </div>
                    <div>
                        <h2 className="font-display text-3xl font-bold tracking-tight md:text-4xl">
                            Built for business, cooked for the soul
                        </h2>
                        <p className="mt-4 text-muted-foreground md:text-lg">
                            Office and event food is as much about reliable
                            delivery as taste. We handle daily lunches and
                            large orders without compromising authentic
                            Pakistani cuisine.
                        </p>
                        <ul className="mt-8 space-y-5">
                            {[
                                {
                                    title: 'Reliable operations',
                                    body: 'Punctual deliveries that respect your meeting schedules.',
                                },
                                {
                                    title: 'Authentic taste',
                                    body: 'Traditional methods and carefully sourced spices.',
                                },
                                {
                                    title: 'Hygiene first',
                                    body: 'Strict quality control in a professional kitchen.',
                                },
                            ].map((item) => (
                                <li
                                    key={item.title}
                                    className="flex items-start gap-3"
                                >
                                    <CheckCircle2 className="mt-0.5 size-5 shrink-0 text-emerald-600" />
                                    <div>
                                        <p className="font-semibold">
                                            {item.title}
                                        </p>
                                        <p className="text-sm text-muted-foreground">
                                            {item.body}
                                        </p>
                                    </div>
                                </li>
                            ))}
                        </ul>
                        <Button asChild variant="outline" className="mt-8 min-h-11">
                            <Link href="/about">About {brand}</Link>
                        </Button>
                    </div>
                </div>
            </section>
        </>
    );
}

function PathCard({
    icon,
    title,
    body,
    eyebrow,
    cta,
    href,
    iconClass,
    featured = false,
}: {
    icon: ReactNode;
    title: string;
    body: string;
    eyebrow: string;
    cta: string;
    href: string;
    iconClass: string;
    featured?: boolean;
}) {
    return (
        <div
            className={cn(
                'flex h-full flex-col rounded-lg border border-border/80 bg-card p-7 transition-transform duration-200 hover:-translate-y-0.5 hover:shadow-md',
                featured && 'border-primary/30',
            )}
        >
            <div
                className={cn(
                    'mb-5 flex size-12 items-center justify-center rounded-full',
                    iconClass,
                )}
            >
                {icon}
            </div>
            <h3 className="font-display text-2xl font-bold tracking-tight">
                {title}
            </h3>
            <p className="mt-2 flex-1 text-sm leading-relaxed text-muted-foreground">
                {body}
            </p>
            <div className="mt-6 border-t border-border/70 pt-5">
                <p className="text-[11px] font-bold tracking-[0.08em] text-primary uppercase">
                    {eyebrow}
                </p>
                <Link
                    href={href}
                    className="mt-2 inline-flex items-center gap-2 text-sm font-bold transition-transform hover:translate-x-0.5"
                >
                    {cta}
                    <ArrowRight className="size-4" />
                </Link>
            </div>
        </div>
    );
}
