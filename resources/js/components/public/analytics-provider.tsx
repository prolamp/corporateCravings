import { usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import {
    disableGoogleAnalytics,
    getAnalyticsConsent,
    loadGoogleAnalytics,
    trackPageView,
    trackWhatsAppClick,
    type AnalyticsConsent,
} from '@/lib/analytics';
import { AnalyticsConsentBanner } from '@/components/public/analytics-consent-banner';

function measurementIdFromPage(): string | null {
    const { site } = usePage().props;
    const id = site?.analytics?.ga_measurement_id;

    return typeof id === 'string' && id.length > 0 ? id : null;
}

export function AnalyticsProvider() {
    const { url } = usePage();
    const measurementId = measurementIdFromPage();
    const [consent, setConsent] = useState<AnalyticsConsent | null>(null);
    const [ready, setReady] = useState(false);

    useEffect(() => {
        setConsent(getAnalyticsConsent());
        setReady(true);

        const onConsent = (event: Event) => {
            const detail = (event as CustomEvent<AnalyticsConsent | null>)
                .detail;
            setConsent(detail ?? null);
        };

        window.addEventListener('cc:analytics-consent', onConsent);
        return () =>
            window.removeEventListener('cc:analytics-consent', onConsent);
    }, []);

    useEffect(() => {
        if (!ready || !measurementId) {
            return;
        }

        if (consent === 'granted') {
            loadGoogleAnalytics(measurementId);
            trackPageView(url);
        } else if (consent === 'denied') {
            disableGoogleAnalytics();
        }
    }, [ready, consent, measurementId, url]);

    useEffect(() => {
        if (!ready) {
            return;
        }

        function onClick(event: MouseEvent) {
            const target = event.target as Element | null;
            const anchor = target?.closest?.(
                'a[href*="wa.me"]',
            ) as HTMLAnchorElement | null;

            if (!anchor) {
                return;
            }

            const placement =
                anchor.getAttribute('data-wa-placement') || 'whatsapp_link';
            trackWhatsAppClick(placement);
        }

        document.addEventListener('click', onClick);
        return () => document.removeEventListener('click', onClick);
    }, [ready]);

    if (!measurementId) {
        return null;
    }

    return (
        <AnalyticsConsentBanner visible={ready && consent === null} />
    );
}
