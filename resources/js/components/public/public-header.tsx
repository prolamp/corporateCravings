import { Link, usePage } from '@inertiajs/react';
import { Menu, ShoppingCart } from 'lucide-react';
import { useState } from 'react';
import { AppearanceToggle } from '@/components/appearance-toggle';
import { BrandLogo } from '@/components/public/brand-logo';
import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import {
    isCustomer,
    isManagementUser,
    isSubscriber,
} from '@/lib/roles';
import { publicPrimaryNav } from '@/lib/public-nav';
import { cn } from '@/lib/utils';
import { login } from '@/routes';
import type { Auth } from '@/types';

function accountHref(auth: Auth): string {
    const user = auth.user;
    if (!user) {
        return login.url();
    }
    if (isManagementUser(user)) {
        return '/dashboard';
    }
    if (isSubscriber(user)) {
        return '/subscriber/dashboard';
    }
    if (isCustomer(user)) {
        return '/customer/dashboard';
    }

    return '/dashboard';
}

export function PublicHeader({ currentPath = '/' }: { currentPath?: string }) {
    const { auth, site, cart } = usePage().props;
    const [open, setOpen] = useState(false);
    const brand = site?.brand?.name ?? 'Corporate Cravings';
    const accountLabel = auth.user ? 'Account' : 'Login';
    const accountUrl = accountHref(auth);
    const cartCount = cart?.count ?? 0;

    return (
        <header className="sticky top-0 z-50 border-b border-border/80 bg-background/95 backdrop-blur supports-backdrop-filter:bg-background/80">
            <nav className="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 md:h-20 md:px-10">
                <Link
                    href="/"
                    className="flex shrink-0 items-center"
                    aria-label={brand}
                >
                    <BrandLogo className="h-7 md:h-9" />
                </Link>

                <div className="hidden items-center gap-6 lg:flex">
                    {publicPrimaryNav.map((item) => {
                        const active =
                            item.href === '/'
                                ? currentPath === '/'
                                : currentPath.startsWith(item.href);

                        return (
                            <Link
                                key={item.href}
                                href={item.href}
                                className={cn(
                                    'text-sm font-medium transition-colors',
                                    active
                                        ? 'border-b-2 border-primary pb-0.5 text-primary'
                                        : 'text-muted-foreground hover:text-foreground',
                                )}
                            >
                                {item.title}
                            </Link>
                        );
                    })}
                </div>

                <div className="flex items-center gap-1 sm:gap-2">
                    <AppearanceToggle />
                    <Button variant="ghost" size="icon" asChild className="relative">
                        <Link href="/cart" aria-label={`Cart${cartCount ? ` (${cartCount})` : ''}`}>
                            <ShoppingCart className="size-5" />
                            {cartCount > 0 && (
                                <span className="absolute top-1 right-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-primary px-1 text-[10px] font-bold text-primary-foreground">
                                    {cartCount > 99 ? '99+' : cartCount}
                                </span>
                            )}
                        </Link>
                    </Button>
                    <Button
                        asChild
                        className="hidden min-h-11 px-5 md:inline-flex"
                    >
                        <Link href={accountUrl}>{accountLabel}</Link>
                    </Button>

                    <Sheet open={open} onOpenChange={setOpen}>
                        <SheetTrigger asChild>
                            <Button
                                variant="ghost"
                                size="icon"
                                className="lg:hidden"
                                aria-label="Open menu"
                            >
                                <Menu className="size-5" />
                            </Button>
                        </SheetTrigger>
                        <SheetContent side="right" className="w-[min(100%,20rem)]">
                            <SheetHeader>
                                <SheetTitle className="text-left">
                                    <BrandLogo className="h-8" alt={brand} />
                                </SheetTitle>
                            </SheetHeader>
                            <div className="mt-6 flex flex-col gap-1">
                                {publicPrimaryNav.map((item) => (
                                    <Link
                                        key={item.href}
                                        href={item.href}
                                        onClick={() => setOpen(false)}
                                        className="rounded-md px-3 py-3 text-base font-medium text-foreground hover:bg-muted"
                                    >
                                        {item.title}
                                    </Link>
                                ))}
                                <Link
                                    href="/cart"
                                    onClick={() => setOpen(false)}
                                    className="rounded-md px-3 py-3 text-base font-medium text-foreground hover:bg-muted"
                                >
                                    Cart{cartCount > 0 ? ` (${cartCount})` : ''}
                                </Link>
                                <Link
                                    href={accountUrl}
                                    onClick={() => setOpen(false)}
                                    className="mt-2 inline-flex min-h-11 items-center justify-center rounded-md bg-primary px-4 text-sm font-semibold text-primary-foreground"
                                >
                                    {accountLabel}
                                </Link>
                                {site?.whatsapp_url && (
                                    <a
                                        href={site.whatsapp_url}
                                        target="_blank"
                                        rel="noreferrer"
                                        data-wa-placement="header"
                                        className="mt-2 inline-flex min-h-11 items-center justify-center rounded-md bg-[#25D366] px-4 text-sm font-semibold text-white"
                                    >
                                        WhatsApp Us
                                    </a>
                                )}
                            </div>
                        </SheetContent>
                    </Sheet>
                </div>
            </nav>
        </header>
    );
}
