import { Link, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { logout } from '@/routes';

const items = [
    { title: 'Dashboard', href: '/customer/dashboard' },
    { title: 'Order history', href: '/customer/orders' },
] as const;

export function CustomerNav({ current }: { current: string }) {
    function handleLogout() {
        router.flushAll();
        router.post(logout.url());
    }

    return (
        <nav className="mb-8 flex flex-wrap items-center gap-2">
            {items.map((item) => {
                const active = current === item.href;

                return (
                    <Link
                        key={item.href}
                        href={item.href}
                        className={cn(
                            'inline-flex min-h-10 items-center rounded-md px-4 text-sm font-medium transition-colors',
                            active
                                ? 'bg-primary text-primary-foreground'
                                : 'border border-border/80 bg-card text-muted-foreground hover:text-foreground',
                        )}
                    >
                        {item.title}
                    </Link>
                );
            })}
            <Button
                type="button"
                variant="outline"
                className="min-h-10"
                onClick={handleLogout}
                data-test="logout-button"
            >
                Log out
            </Button>
        </nav>
    );
}
