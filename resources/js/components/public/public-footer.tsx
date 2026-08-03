import { Link, usePage } from '@inertiajs/react';
import { BrandLogo } from '@/components/public/brand-logo';
import {
    publicFooterCompany,
    publicFooterServices,
} from '@/lib/public-nav';
import { clearAnalyticsConsent } from '@/lib/analytics';

export function PublicFooter() {
    const { site } = usePage().props;
    const brand = site?.brand;
    const name = brand?.name ?? 'Corporate Cravings';
    const year = new Date().getFullYear();
    const hasAnalytics = Boolean(site?.analytics?.ga_measurement_id);

    return (
        <footer className="border-t border-border/80 bg-muted/40">
            <div className="mx-auto grid max-w-7xl gap-10 px-4 py-14 md:grid-cols-4 md:px-10 md:py-16">
                <div className="md:col-span-1">
                    <Link href="/" aria-label={name} className="inline-block">
                        <BrandLogo className="h-9 md:h-10" alt={name} />
                    </Link>
                    <p className="mt-3 text-sm leading-relaxed text-muted-foreground">
                        Fresh Pakistani meals for Islamabad offices and
                        events—daily lunches, packages, and bulk orders.
                    </p>
                    {brand?.hours && (
                        <p className="mt-4 text-sm text-muted-foreground">
                            {brand.hours}
                        </p>
                    )}
                </div>

                <div>
                    <p className="text-[11px] font-bold tracking-[0.08em] text-primary uppercase">
                        Services
                    </p>
                    <ul className="mt-4 space-y-3">
                        {publicFooterServices.map((item) => (
                            <li key={item.href}>
                                <Link
                                    href={item.href}
                                    className="text-sm text-muted-foreground transition-colors hover:text-foreground"
                                >
                                    {item.title}
                                </Link>
                            </li>
                        ))}
                    </ul>
                </div>

                <div>
                    <p className="text-[11px] font-bold tracking-[0.08em] text-primary uppercase">
                        Company
                    </p>
                    <ul className="mt-4 space-y-3">
                        {publicFooterCompany.map((item) => (
                            <li key={item.href}>
                                <Link
                                    href={item.href}
                                    className="text-sm text-muted-foreground transition-colors hover:text-foreground"
                                >
                                    {item.title}
                                </Link>
                            </li>
                        ))}
                    </ul>
                </div>

                <div>
                    <p className="text-[11px] font-bold tracking-[0.08em] text-primary uppercase">
                        Support
                    </p>
                    <ul className="mt-4 space-y-3 text-sm text-muted-foreground">
                        {site?.whatsapp_url && (
                            <li>
                                <a
                                    href={site.whatsapp_url}
                                    target="_blank"
                                    rel="noreferrer"
                                    data-wa-placement="footer"
                                    className="font-medium text-[#128C7E] transition-colors hover:text-[#075E54]"
                                >
                                    WhatsApp Support
                                </a>
                            </li>
                        )}
                        {brand?.address && <li>{brand.address}</li>}
                        {brand?.email && (
                            <li>
                                <a
                                    href={`mailto:${brand.email}`}
                                    className="hover:text-foreground"
                                >
                                    {brand.email}
                                </a>
                            </li>
                        )}
                        {brand?.phone && (
                            <li>
                                <a
                                    href={`tel:${brand.phone.replace(/\s+/g, '')}`}
                                    className="hover:text-foreground"
                                >
                                    {brand.phone}
                                </a>
                            </li>
                        )}
                        {hasAnalytics && (
                            <li>
                                <button
                                    type="button"
                                    className="text-left hover:text-foreground"
                                    onClick={() => clearAnalyticsConsent()}
                                >
                                    Cookie preferences
                                </button>
                            </li>
                        )}
                    </ul>
                </div>
            </div>

            <div className="border-t border-border/60">
                <p className="mx-auto max-w-7xl px-4 py-6 text-center text-xs text-muted-foreground md:px-10 md:text-left">
                    © {year} {name}. Islamabad, Pakistan.
                </p>
            </div>
        </footer>
    );
}
