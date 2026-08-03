/**
 * Consent-aware GA4 helpers. Never send names, phones, addresses, or order numbers.
 */

export type AnalyticsConsent = 'granted' | 'denied';

const CONSENT_STORAGE_KEY = 'cc_analytics_consent';

type Gtag = (...args: unknown[]) => void;

declare global {
    interface Window {
        dataLayer?: unknown[];
        gtag?: Gtag;
        __ccGaId?: string;
        __ccGaLoaded?: boolean;
    }
}

export function getAnalyticsConsent(): AnalyticsConsent | null {
    if (typeof window === 'undefined') {
        return null;
    }

    try {
        const value = window.localStorage.getItem(CONSENT_STORAGE_KEY);
        if (value === 'granted' || value === 'denied') {
            return value;
        }
    } catch {
        // ignore storage errors
    }

    return null;
}

export function setAnalyticsConsent(value: AnalyticsConsent): void {
    try {
        window.localStorage.setItem(CONSENT_STORAGE_KEY, value);
    } catch {
        // ignore storage errors
    }

    window.dispatchEvent(
        new CustomEvent('cc:analytics-consent', { detail: value }),
    );
}

export function clearAnalyticsConsent(): void {
    try {
        window.localStorage.removeItem(CONSENT_STORAGE_KEY);
    } catch {
        // ignore
    }

    window.dispatchEvent(
        new CustomEvent('cc:analytics-consent', { detail: null }),
    );
}

function ensureGtagStub(): void {
    window.dataLayer = window.dataLayer || [];
    if (typeof window.gtag !== 'function') {
        window.gtag = function gtag(...args: unknown[]) {
            window.dataLayer?.push(args);
        };
    }
}

export function loadGoogleAnalytics(measurementId: string): void {
    if (typeof window === 'undefined' || !measurementId) {
        return;
    }

    ensureGtagStub();

    if (!window.__ccGaLoaded) {
        window.gtag?.('consent', 'default', {
            analytics_storage: 'denied',
            ad_storage: 'denied',
            wait_for_update: 500,
        });
    }

    if (window.__ccGaLoaded && window.__ccGaId === measurementId) {
        window.gtag?.('consent', 'update', {
            analytics_storage: 'granted',
        });
        return;
    }

    window.__ccGaId = measurementId;

    window.gtag?.('js', new Date());
    window.gtag?.('config', measurementId, {
        anonymize_ip: true,
        send_page_view: false,
    });
    window.gtag?.('consent', 'update', {
        analytics_storage: 'granted',
    });

    if (!document.getElementById('cc-ga4-script')) {
        const script = document.createElement('script');
        script.id = 'cc-ga4-script';
        script.async = true;
        script.src = `https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(measurementId)}`;
        document.head.appendChild(script);
    }

    window.__ccGaLoaded = true;
}

export function disableGoogleAnalytics(): void {
    if (typeof window === 'undefined') {
        return;
    }

    ensureGtagStub();
    window.gtag?.('consent', 'update', {
        analytics_storage: 'denied',
    });
}

/** Safe event params only — strings/numbers/booleans, no free-form PII. */
export type AnalyticsParams = Record<
    string,
    string | number | boolean | undefined
>;

export function trackEvent(
    eventName: string,
    params: AnalyticsParams = {},
): void {
    if (typeof window === 'undefined') {
        return;
    }

    if (getAnalyticsConsent() !== 'granted' || !window.__ccGaLoaded) {
        return;
    }

    const cleaned: Record<string, string | number | boolean> = {};
    for (const [key, value] of Object.entries(params)) {
        if (value !== undefined) {
            cleaned[key] = value;
        }
    }

    window.gtag?.('event', eventName, cleaned);
}

export function trackPageView(path: string): void {
    trackEvent('page_view', {
        page_path: path.split('?')[0] || '/',
    });
}

export function trackWhatsAppClick(placement: string): void {
    trackEvent('whatsapp_click', {
        placement: placement.slice(0, 64),
    });
}

export function trackAddToCart(input: {
    item_id: string;
    quantity: number;
    value?: number;
}): void {
    trackEvent('add_to_cart', {
        currency: 'PKR',
        value: input.value,
        items_count: 1,
        item_id: input.item_id.slice(0, 64),
        quantity: input.quantity,
    });
}

export function trackBeginCheckout(input: {
    items_count: number;
    value?: number;
}): void {
    trackEvent('begin_checkout', {
        currency: 'PKR',
        value: input.value,
        items_count: input.items_count,
    });
}

export function trackPurchase(input: {
    items_count: number;
    value?: number;
}): void {
    trackEvent('purchase', {
        currency: 'PKR',
        value: input.value,
        items_count: input.items_count,
    });
}
