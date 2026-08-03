import { Link, usePage } from '@inertiajs/react';
import {
    ArrowRight,
    Crosshair,
    Factory,
    ShieldCheck,
    Truck,
    UtensilsCrossed,
    Award,
} from 'lucide-react';
import type { ReactNode } from 'react';
import { SeoHead } from '@/components/public/seo-head';
import { Button } from '@/components/ui/button';
import { withOrganization } from '@/lib/seo-jsonld';
import { cn } from '@/lib/utils';

type Stat = { value: string; label: string };
type Value = { title: string; body: string };

type Props = {
    hero_image_url: string;
    story_image_url: string;
    operations_image_url: string;
    partner_image_url: string;
    story_stats: Stat[];
    impact_stats: Stat[];
    values: Value[];
};

const valueIcons: Record<string, ReactNode> = {
    Authenticity: <Award className="size-7" />,
    Precision: <Crosshair className="size-7" />,
    Reliability: <ShieldCheck className="size-7" />,
};

export default function PublicAbout({
    hero_image_url,
    story_image_url,
    operations_image_url,
    partner_image_url,
    story_stats,
    impact_stats,
    values,
}: Props) {
    const { site, seo } = usePage().props;
    const brand = site?.brand?.name ?? 'Corporate Cravings';
    const whatsappUrl = site?.whatsapp_url;

    return (
        <>
            <SeoHead
                title="About"
                description={`Learn about ${brand}—fresh Pakistani catering for Islamabad offices, events, and bulk orders.`}
                image={hero_image_url}
                path="/about"
                jsonLd={withOrganization(seo)}
            />

            {/* Hero */}
            <section className="relative flex min-h-[min(780px,90svh)] items-center overflow-hidden">
                <div className="absolute inset-0">
                    <img
                        src={hero_image_url}
                        alt=""
                        className="size-full object-cover"
                    />
                    <div className="absolute inset-0 bg-linear-to-r from-black/85 via-black/50 to-black/20" />
                </div>
                <div className="relative z-10 mx-auto w-full max-w-7xl px-4 py-20 md:px-10 md:py-28">
                    <div className="max-w-2xl animate-in fade-in slide-in-from-bottom-4 duration-700">
                        <p className="font-display text-4xl font-bold tracking-tight text-[#f6c60c] sm:text-5xl md:text-6xl">
                            {brand}
                        </p>
                        <p className="mt-5 text-[11px] font-bold tracking-[0.14em] text-[#ffe089] uppercase">
                            Our legacy
                        </p>
                        <h1 className="mt-3 font-display text-3xl font-bold tracking-tight text-white sm:text-4xl md:text-[2.75rem] md:leading-tight">
                            Crafting heritage for the modern workspace
                        </h1>
                        <p className="mt-4 max-w-lg text-base leading-relaxed text-white/90 sm:text-lg">
                            Bringing the refined elegance of Pakistani culinary
                            tradition to the professional landscape, one plate
                            at a time.
                        </p>
                        <div className="mt-8 flex flex-col gap-3 sm:flex-row sm:gap-4">
                            <Button
                                asChild
                                size="lg"
                                className="min-h-12 bg-[#f6c60c] px-8 font-bold text-[#695300] hover:bg-[#ffe089]"
                            >
                                <Link href="/menu">
                                    Discover our menu
                                    <UtensilsCrossed className="size-4" />
                                </Link>
                            </Button>
                            <Button
                                asChild
                                size="lg"
                                variant="outline"
                                className="min-h-12 border-white/40 bg-white/5 text-white backdrop-blur-sm hover:bg-white/15 hover:text-white"
                            >
                                <a href="#story">Our story</a>
                            </Button>
                        </div>
                    </div>
                </div>
            </section>

            {/* Story */}
            <section
                id="story"
                className="scroll-mt-24 bg-[#FDFBF7] py-16 md:py-24 dark:bg-muted/20"
            >
                <div className="mx-auto grid max-w-7xl items-center gap-12 px-4 md:px-10 lg:grid-cols-2 lg:gap-16">
                    <div className="order-2 animate-in fade-in slide-in-from-bottom-3 duration-700 lg:order-1">
                        <p className="text-[11px] font-bold tracking-[0.12em] text-primary uppercase">
                            The Saffron &amp; Slate philosophy
                        </p>
                        <h2 className="mt-3 font-display text-3xl font-bold tracking-tight md:text-4xl">
                            A journey of flavor and precision
                        </h2>
                        <div className="mt-6 space-y-4 text-muted-foreground md:text-lg">
                            <p>
                                Our story began with a simple observation:
                                corporate catering often sacrificed soul for
                                efficiency. We envisioned a different path—one
                                where the rich, aromatic heritage of Pakistan
                                meets the rigorous demands of modern business.
                            </p>
                            <p>
                                The Saffron &amp; Slate philosophy represents
                                this duality. Saffron signifies artisanal
                                culinary tradition and hospitality; Slate
                                represents the structured, reliable corporate
                                environment.
                            </p>
                            <p>
                                Today we serve as the culinary bridge between
                                these worlds, ensuring every professional meal
                                is an experience worth remembering.
                            </p>
                        </div>
                        <div className="mt-10 flex gap-10 border-t border-border/80 pt-8">
                            {story_stats.map((stat) => (
                                <div key={stat.label}>
                                    <p className="font-display text-3xl font-bold text-primary md:text-4xl">
                                        {stat.value}
                                    </p>
                                    <p className="mt-1 text-[11px] font-bold tracking-[0.08em] text-muted-foreground uppercase">
                                        {stat.label}
                                    </p>
                                </div>
                            ))}
                        </div>
                    </div>
                    <div className="relative order-1 lg:order-2">
                        <div className="overflow-hidden rounded-lg shadow-lg">
                            <img
                                src={story_image_url}
                                alt={`${brand} gourmet meal presentation`}
                                className="aspect-4/5 w-full object-cover"
                            />
                        </div>
                        <div className="absolute -bottom-5 -left-3 hidden items-center gap-3 rounded-lg border border-border/80 bg-background px-4 py-3 shadow-md md:flex lg:-left-5">
                            <div className="flex size-10 items-center justify-center rounded-full bg-primary/10 text-primary">
                                <UtensilsCrossed className="size-4" />
                            </div>
                            <div>
                                <p className="text-sm font-semibold">
                                    Artisanal quality
                                </p>
                                <p className="text-xs text-muted-foreground">
                                    Daily sourced ingredients
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* Built for business */}
            <section className="border-y border-border/80 bg-muted/30 py-16 md:py-24">
                <div className="mx-auto max-w-7xl px-4 md:px-10">
                    <div className="mx-auto max-w-3xl text-center">
                        <h2 className="font-display text-3xl font-bold tracking-tight md:text-4xl">
                            Built for business
                        </h2>
                        <p className="mt-4 text-muted-foreground md:text-lg">
                            Catering for busy teams requires more than good
                            food—it needs logistical excellence and
                            uncompromising standards.
                        </p>
                    </div>
                    <div className="mt-12 grid gap-6 md:grid-cols-3">
                        <FeaturePanel
                            icon={<Factory className="size-8" />}
                            title="Industrial excellence"
                            body="Our commercial kitchen is the heart of operations, combining traditional cooking methods with advanced safety protocols."
                            href="/packages"
                            cta="View packages"
                        />
                        <div className="overflow-hidden rounded-lg border border-border/80">
                            <img
                                src={operations_image_url}
                                alt={`${brand} commercial kitchen preparing fresh trays`}
                                className="h-full min-h-64 w-full object-cover"
                            />
                        </div>
                        <FeaturePanel
                            icon={<Truck className="size-8" />}
                            title="Fleet logistics"
                            body="Temperature-aware delivery so every dish arrives as the kitchen intended—fresh, hot, and ready to serve."
                            href="/bulk-orders"
                            cta="Bulk orders"
                        />
                    </div>
                </div>
            </section>

            {/* Preferred partner */}
            <section className="relative overflow-hidden py-20 md:py-28">
                <div className="absolute inset-0">
                    <img
                        src={partner_image_url}
                        alt=""
                        className="size-full object-cover brightness-[0.35]"
                    />
                </div>
                <div className="relative z-10 mx-auto max-w-7xl px-4 md:px-10">
                    <div className="grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
                        <div className="text-white">
                            <h2 className="font-display text-3xl font-bold tracking-tight md:text-4xl md:leading-tight">
                                The preferred partner for the professional
                                palate
                            </h2>
                            <p className="mt-5 max-w-lg text-base text-white/80 md:text-lg">
                                From daily office lunches to high-stakes
                                executive dinners, we bring care and
                                sophistication that lifts workplace culture.
                            </p>
                            {whatsappUrl && (
                                <Button
                                    asChild
                                    size="lg"
                                    className="mt-8 min-h-12 bg-white px-8 font-bold text-foreground hover:bg-[#FDFBF7]"
                                >
                                    <a
                                        href={whatsappUrl}
                                        target="_blank"
                                        rel="noreferrer"
                                    >
                                        Become a partner
                                        <ArrowRight className="size-4" />
                                    </a>
                                </Button>
                            )}
                        </div>
                        <div className="grid gap-4 sm:grid-cols-3">
                            {impact_stats.map((stat) => (
                                <div
                                    key={stat.label}
                                    className="rounded-lg border border-white/20 bg-white/10 px-5 py-8 text-center text-white backdrop-blur-md"
                                >
                                    <p className="font-display text-3xl font-bold">
                                        {stat.value}
                                    </p>
                                    <p className="mt-2 text-[11px] font-bold tracking-[0.1em] text-white/75 uppercase">
                                        {stat.label}
                                    </p>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </section>

            {/* Values */}
            <section className="bg-[#FDFBF7] py-16 md:py-24 dark:bg-background">
                <div className="mx-auto max-w-7xl px-4 md:px-10">
                    <div className="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                        <div className="max-w-xl">
                            <p className="text-[11px] font-bold tracking-[0.12em] text-primary uppercase">
                                Our foundation
                            </p>
                            <h2 className="mt-3 font-display text-3xl font-bold tracking-tight md:text-4xl">
                                Driven by excellence, rooted in values
                            </h2>
                        </div>
                        <p className="max-w-sm text-muted-foreground">
                            Every plate we serve is guided by three pillars that
                            define our commitment to your workplace.
                        </p>
                    </div>
                    <ul className="mt-12 grid gap-6 md:grid-cols-3">
                        {values.map((item) => (
                            <li
                                key={item.title}
                                className="border-t border-border/80 pt-8"
                            >
                                <div className="mb-5 flex size-14 items-center justify-center rounded-lg bg-[#f6c60c]/20 text-primary">
                                    {valueIcons[item.title] ?? (
                                        <ShieldCheck className="size-7" />
                                    )}
                                </div>
                                <h3 className="font-display text-xl font-semibold tracking-tight">
                                    {item.title}
                                </h3>
                                <p className="mt-3 text-sm leading-relaxed text-muted-foreground md:text-base">
                                    {item.body}
                                </p>
                            </li>
                        ))}
                    </ul>
                </div>
            </section>

            {/* CTA */}
            <section className="py-16 md:py-20">
                <div className="mx-auto max-w-7xl px-4 md:px-10">
                    <div className="relative overflow-hidden rounded-2xl bg-[#111827] px-8 py-14 text-center text-white md:px-16 md:py-20">
                        <div
                            className="pointer-events-none absolute inset-0 opacity-15"
                            aria-hidden
                        >
                            <svg
                                className="size-full"
                                preserveAspectRatio="none"
                                viewBox="0 0 100 100"
                            >
                                <path
                                    d="M0 0 L100 100 M100 0 L0 100"
                                    fill="none"
                                    stroke="white"
                                    strokeWidth="0.15"
                                />
                            </svg>
                        </div>
                        <div className="relative z-10">
                            <h2 className="font-display text-3xl font-bold tracking-tight text-balance md:text-4xl">
                                Elevate your corporate dining experience
                            </h2>
                            <p className="mx-auto mt-4 max-w-2xl text-base text-white/70 md:text-lg">
                                Partner with {brand} to bring heritage flavour
                                and professional excellence to your workspace.
                            </p>
                            <div className="mt-8 flex flex-col justify-center gap-3 sm:flex-row sm:gap-4">
                                <Button
                                    asChild
                                    size="lg"
                                    className="min-h-12 bg-[#f6c60c] px-8 font-bold text-[#695300] hover:bg-[#ffe089]"
                                >
                                    <Link href="/packages">
                                        Schedule a tasting
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
                                            Talk to an expert
                                        </a>
                                    </Button>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </>
    );
}

function FeaturePanel({
    icon,
    title,
    body,
    href,
    cta,
}: {
    icon: ReactNode;
    title: string;
    body: string;
    href: string;
    cta: string;
}) {
    return (
        <div
            className={cn(
                'flex flex-col justify-between border border-border/80 bg-background p-8 md:p-10',
            )}
        >
            <div>
                <div className="mb-6 text-primary">{icon}</div>
                <h3 className="font-display text-xl font-semibold tracking-tight">
                    {title}
                </h3>
                <p className="mt-3 text-sm leading-relaxed text-muted-foreground">
                    {body}
                </p>
            </div>
            <Link
                href={href}
                className="mt-8 inline-flex items-center gap-2 text-[11px] font-bold tracking-[0.08em] text-primary uppercase hover:underline"
            >
                {cta}
                <ArrowRight className="size-3.5" />
            </Link>
        </div>
    );
}
