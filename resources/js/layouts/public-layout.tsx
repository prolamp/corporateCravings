import { usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { FlashToast } from '@/components/flash-toast/flash-toast';
import { AnalyticsProvider } from '@/components/public/analytics-provider';
import { PublicFooter } from '@/components/public/public-footer';
import { PublicHeader } from '@/components/public/public-header';
import { WhatsappFab } from '@/components/public/whatsapp-fab';

export default function PublicLayout({ children }: { children: ReactNode }) {
    const { url } = usePage();
    const path = url.split('?')[0] || '/';

    return (
        <div className="flex min-h-screen flex-col bg-background text-foreground">
            <PublicHeader currentPath={path} />
            <main className="flex-1">{children}</main>
            <PublicFooter />
            <WhatsappFab />
            <AnalyticsProvider />
            <FlashToast />
        </div>
    );
}
