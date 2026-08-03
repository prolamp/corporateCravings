import { Link, usePage } from '@inertiajs/react';
import { Check, ChevronRight } from 'lucide-react';
import { SeoHead } from '@/components/public/seo-head';
import { Button } from '@/components/ui/button';
import {
    absoluteUrl,
    breadcrumbJsonLd,
    packageProductJsonLd,
} from '@/lib/seo-jsonld';

type PackageItem = {
    id: number;
    dish_title: string | null;
    variation_label: string | null;
};

type Props = {
    package: {
        id: number;
        title: string;
        slug: string;
        short_description: string | null;
        image_url: string | null;
        price_per_person: number;
        price_per_person_formatted: string;
        base_amount_per_person_formatted: string;
        has_discount: boolean;
        items: PackageItem[];
        items_count: number;
        href: string;
    };
};

export default function PackageShow({ package: pkg }: Props) {
    const { site, seo } = usePage().props;
    const whatsappUrl = site?.whatsapp_url;
    const brand = site?.brand?.name ?? 'Corporate Cravings';
    const packagePath = `/packages/${pkg.slug}`;
    const packageUrl = absoluteUrl(seo?.app_url ?? '', packagePath);

    function quoteHref(): string | null {
        if (!whatsappUrl) {
            return null;
        }

        const message = encodeURIComponent(
            `Hi ${brand} — I'd like a quote for the ${pkg.title} package (${pkg.price_per_person_formatted} / person).`,
        );

        return `${whatsappUrl}?text=${message}`;
    }

    const href = quoteHref();

    return (
        <>
            <SeoHead
                title={pkg.title}
                description={
                    pkg.short_description ||
                    `${pkg.title} corporate meal package from ${brand}—${pkg.price_per_person_formatted} per person.`
                }
                image={pkg.image_url}
                path={packagePath}
                type="product"
                jsonLd={[
                    packageProductJsonLd({
                        name: pkg.title,
                        description: pkg.short_description,
                        image: pkg.image_url,
                        url: packageUrl,
                        pricePerPerson: pkg.price_per_person,
                    }),
                    breadcrumbJsonLd([
                        {
                            name: 'Home',
                            url: absoluteUrl(seo?.app_url ?? '', '/'),
                        },
                        {
                            name: 'Packages',
                            url: absoluteUrl(seo?.app_url ?? '', '/packages'),
                        },
                        { name: pkg.title, url: packageUrl },
                    ]),
                ]}
            />

            <div className="mx-auto max-w-7xl px-4 py-8 pb-28 md:px-10 md:py-12 md:pb-12">
                <nav className="mb-8 flex flex-wrap items-center gap-1 text-[11px] font-bold tracking-[0.08em] text-muted-foreground uppercase">
                    <Link href="/packages" className="hover:text-primary">
                        Packages
                    </Link>
                    <ChevronRight className="size-3.5" />
                    <span className="text-primary">{pkg.title}</span>
                </nav>

                <div className="grid gap-10 lg:grid-cols-12 lg:gap-12">
                    <div className="space-y-8 lg:col-span-7">
                        <div className="aspect-4/3 overflow-hidden rounded-2xl bg-muted shadow-sm">
                            {pkg.image_url ? (
                                <img
                                    src={pkg.image_url}
                                    alt={pkg.title}
                                    className="size-full object-cover"
                                />
                            ) : (
                                <div className="flex size-full items-center justify-center text-sm text-muted-foreground">
                                    No image
                                </div>
                            )}
                        </div>

                        <div>
                            <p className="mb-2 text-[11px] font-bold tracking-[0.08em] text-primary uppercase">
                                Corporate package
                            </p>
                            <h1 className="font-display text-3xl font-bold tracking-tight md:text-5xl">
                                {pkg.title}
                            </h1>
                            {pkg.short_description && (
                                <p className="mt-4 max-w-2xl text-base leading-relaxed text-muted-foreground md:text-lg">
                                    {pkg.short_description}
                                </p>
                            )}
                        </div>

                        <section>
                            <h2 className="text-xl font-semibold tracking-tight">
                                What is included
                            </h2>
                            {pkg.items.length === 0 ? (
                                <p className="mt-4 text-sm text-muted-foreground">
                                    Menu details are confirmed when you request
                                    a quote.
                                </p>
                            ) : (
                                <ul className="mt-4 space-y-3">
                                    {pkg.items.map((item) => (
                                        <li
                                            key={item.id}
                                            className="flex items-start gap-3 rounded-lg border border-border/70 bg-card px-4 py-3"
                                        >
                                            <span className="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-full bg-primary/15 text-primary">
                                                <Check className="size-3.5 stroke-[3]" />
                                            </span>
                                            <div>
                                                <p className="font-medium text-foreground">
                                                    {item.dish_title ??
                                                        'Included dish'}
                                                </p>
                                                {item.variation_label && (
                                                    <p className="text-sm text-muted-foreground">
                                                        {item.variation_label}
                                                    </p>
                                                )}
                                            </div>
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </section>
                    </div>

                    <div className="lg:col-span-5">
                        <section className="sticky top-24 rounded-2xl border border-border/80 bg-card p-6 shadow-sm md:p-8">
                            <p className="text-[11px] font-bold tracking-[0.08em] text-muted-foreground uppercase">
                                From
                            </p>
                            <div className="mt-1 flex flex-wrap items-baseline gap-3">
                                <p className="font-display text-4xl font-bold text-primary">
                                    {pkg.price_per_person_formatted}
                                </p>
                                <span className="text-sm text-muted-foreground">
                                    / person
                                </span>
                            </div>
                            {pkg.has_discount && (
                                <p className="mt-1 text-sm text-muted-foreground line-through">
                                    {pkg.base_amount_per_person_formatted}{' '}
                                    / person
                                </p>
                            )}
                            <p className="mt-4 text-sm leading-relaxed text-muted-foreground">
                                Packages are quoted on WhatsApp for your
                                headcount, delivery area, and schedule. Not
                                available in the online cart.
                            </p>

                            {href ? (
                                <Button
                                    asChild
                                    size="lg"
                                    className="mt-6 min-h-12 w-full border-0 bg-linear-to-br from-[#25D366] to-[#128C7E] font-bold text-white hover:opacity-90"
                                >
                                    <a
                                        href={href}
                                        target="_blank"
                                        rel="noreferrer"
                                        data-wa-placement="package_quote"
                                    >
                                        Quote on WhatsApp
                                    </a>
                                </Button>
                            ) : (
                                <p className="mt-6 rounded-lg border border-border/80 px-4 py-3 text-sm text-muted-foreground">
                                    WhatsApp number is not configured yet.
                                    Please use the contact page.
                                </p>
                            )}

                            <Button
                                asChild
                                variant="ghost"
                                className="mt-3 w-full"
                            >
                                <Link href="/packages">
                                    ← All packages
                                </Link>
                            </Button>
                        </section>
                    </div>
                </div>
            </div>

            {href && (
                <div className="fixed inset-x-0 bottom-0 z-40 border-t border-border/80 bg-background/95 p-4 backdrop-blur md:hidden">
                    <div className="mx-auto flex max-w-7xl items-center justify-between gap-4">
                        <div>
                            <p className="text-[11px] font-bold tracking-[0.08em] text-muted-foreground uppercase">
                                Per person
                            </p>
                            <p className="font-display text-xl font-bold text-primary">
                                {pkg.price_per_person_formatted}
                            </p>
                        </div>
                        <Button
                            asChild
                            className="min-h-11 border-0 bg-linear-to-br from-[#25D366] to-[#128C7E] px-5 font-bold text-white hover:opacity-90"
                        >
                            <a
                                href={href}
                                target="_blank"
                                rel="noreferrer"
                                data-wa-placement="package_quote_sticky"
                            >
                                Quote on WhatsApp
                            </a>
                        </Button>
                    </div>
                </div>
            )}
        </>
    );
}
