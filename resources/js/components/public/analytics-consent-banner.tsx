import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { setAnalyticsConsent } from '@/lib/analytics';

type Props = {
    visible: boolean;
    measurementId: string;
};

export function AnalyticsConsentBanner({ visible }: Props) {
    if (!visible) {
        return null;
    }

    function accept() {
        setAnalyticsConsent('granted');
    }

    function decline() {
        setAnalyticsConsent('denied');
    }

    return (
        <div
            role="dialog"
            aria-label="Cookie preferences"
            className="fixed inset-x-0 bottom-0 z-[60] border-t border-border/80 bg-background/95 p-4 shadow-[0_-8px_30px_rgba(0,0,0,0.12)] backdrop-blur-md md:p-5"
        >
            <div className="mx-auto flex max-w-7xl flex-col gap-4 md:flex-row md:items-center md:justify-between md:gap-8">
                <div className="max-w-2xl">
                    <p className="font-display text-base font-semibold tracking-tight">
                        Cookies & analytics
                    </p>
                    <p className="mt-1 text-sm leading-relaxed text-muted-foreground">
                        We use optional Google Analytics to understand how the
                        site is used. No phone numbers, names, addresses, or
                        order details are sent. See our{' '}
                        <Link
                            href="/privacy-policy"
                            className="font-medium text-primary underline-offset-4 hover:underline"
                        >
                            Privacy Policy
                        </Link>
                        .
                    </p>
                </div>
                <div className="flex shrink-0 flex-col gap-2 sm:flex-row">
                    <Button
                        type="button"
                        variant="outline"
                        className="min-h-11"
                        onClick={decline}
                    >
                        Decline
                    </Button>
                    <Button
                        type="button"
                        className="min-h-11 bg-[#f6c60c] font-bold text-[#695300] hover:bg-[#ffe089]"
                        onClick={accept}
                    >
                        Accept analytics
                    </Button>
                </div>
            </div>
        </div>
    );
}
