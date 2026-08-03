import { Link, usePage } from '@inertiajs/react';
import { PublicPackageCard } from '@/components/public/public-package-card';
import { SeoHead } from '@/components/public/seo-head';
import { Button } from '@/components/ui/button';

type PackageCard = {
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
    packages: PackageCard[];
    packages_count: number;
    package_from_formatted: string | null;
};

export default function PublicPackages({
    packages,
    packages_count,
    package_from_formatted,
}: Props) {
    const { site } = usePage().props;
    const whatsappUrl = site?.whatsapp_url;

    return (
        <>
            <SeoHead
                title="Corporate Packages"
                description="Per-person corporate meal packages for Islamabad offices, meetings, and events. Quote on WhatsApp."
                path="/packages"
            />

            <div className="mx-auto max-w-7xl px-4 pt-10 pb-4 md:px-10 md:pt-12">
                <header className="mb-10 md:mb-12">
                    <h1 className="font-display text-4xl font-bold tracking-tight text-foreground md:text-5xl">
                        Corporate Packages
                    </h1>
                    <p className="mt-4 max-w-2xl text-base text-muted-foreground md:text-lg">
                        Per-person meal packages for offices, meetings, and
                        events. Review what is included, then quote on WhatsApp —
                        our team will tailor headcount and delivery.
                    </p>
                    {package_from_formatted && (
                        <p className="mt-4 text-sm font-medium text-foreground">
                            Packages from{' '}
                            <span className="font-display text-lg font-bold text-primary">
                                {package_from_formatted}
                            </span>{' '}
                            / person
                        </p>
                    )}
                </header>
            </div>

            <section className="pb-16 md:pb-20">
                <div className="mx-auto max-w-7xl px-4 md:px-10">
                    <div className="mb-6 flex flex-wrap items-end justify-between gap-4">
                        <p className="text-sm text-muted-foreground">
                            {packages_count}{' '}
                            {packages_count === 1 ? 'package' : 'packages'}
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
                                    Prefer a custom quote? WhatsApp us
                                </a>
                            </Button>
                        )}
                    </div>

                    {packages.length === 0 ? (
                        <p className="mt-6 rounded-lg border border-border/80 px-4 py-12 text-center text-sm text-muted-foreground">
                            Corporate packages will appear here once published.
                            {whatsappUrl ? (
                                <>
                                    {' '}
                                    <a
                                        href={whatsappUrl}
                                        target="_blank"
                                        rel="noreferrer"
                                        className="font-medium text-primary underline-offset-4 hover:underline"
                                    >
                                        Message us on WhatsApp
                                    </a>{' '}
                                    for a tailored proposal.
                                </>
                            ) : null}
                        </p>
                    ) : (
                        <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            {packages.map((pkg) => (
                                <PublicPackageCard key={pkg.id} package={pkg} />
                            ))}
                        </div>
                    )}

                    <p className="mt-12 text-center text-sm text-muted-foreground">
                        Looking for single servings?{' '}
                        <Link
                            href="/menu"
                            className="font-medium text-primary underline-offset-4 hover:underline"
                        >
                            Browse the menu
                        </Link>
                        {' · '}
                        Large kg trays?{' '}
                        <Link
                            href="/bulk-orders"
                            className="font-medium text-primary underline-offset-4 hover:underline"
                        >
                            Bulk orders
                        </Link>
                    </p>
                </div>
            </section>
        </>
    );
}
