import type { JsonLdObject } from '@/components/public/seo-head';

type SeoShared = {
    organization?: Record<string, unknown>;
    app_url?: string;
};

export function withOrganization(
    seo: SeoShared | undefined,
    extra?: JsonLdObject | JsonLdObject[],
): JsonLdObject[] {
    const graphs: JsonLdObject[] = [];
    if (seo?.organization) {
        graphs.push(seo.organization as JsonLdObject);
    }
    if (extra) {
        if (Array.isArray(extra)) {
            graphs.push(...extra);
        } else {
            graphs.push(extra);
        }
    }

    return graphs;
}

export function dishProductJsonLd(input: {
    name: string;
    description?: string | null;
    image?: string | null;
    url: string;
    price?: number | null;
}): JsonLdObject {
    const offer =
        input.price != null
            ? {
                  '@type': 'Offer',
                  priceCurrency: 'PKR',
                  price: Number(input.price).toFixed(0),
                  availability: 'https://schema.org/InStock',
                  url: input.url,
              }
            : undefined;

    return {
        '@context': 'https://schema.org',
        '@type': 'Product',
        name: input.name,
        description: input.description || undefined,
        image: input.image || undefined,
        url: input.url,
        brand: {
            '@type': 'Brand',
            name: 'Corporate Cravings',
        },
        ...(offer ? { offers: offer } : {}),
    };
}

export function packageProductJsonLd(input: {
    name: string;
    description?: string | null;
    image?: string | null;
    url: string;
    pricePerPerson?: number | null;
}): JsonLdObject {
    const offer =
        input.pricePerPerson != null
            ? {
                  '@type': 'Offer',
                  priceCurrency: 'PKR',
                  price: Number(input.pricePerPerson).toFixed(0),
                  availability: 'https://schema.org/InStock',
                  url: input.url,
                  description: 'Per person',
              }
            : undefined;

    return {
        '@context': 'https://schema.org',
        '@type': 'Product',
        name: input.name,
        description: input.description || undefined,
        image: input.image || undefined,
        url: input.url,
        category: 'Corporate meal package',
        ...(offer ? { offers: offer } : {}),
    };
}

export function faqPageJsonLd(
    items: Array<{ question: string; answer: string }>,
): JsonLdObject {
    return {
        '@context': 'https://schema.org',
        '@type': 'FAQPage',
        mainEntity: items.map((item) => ({
            '@type': 'Question',
            name: item.question,
            acceptedAnswer: {
                '@type': 'Answer',
                text: item.answer,
            },
        })),
    };
}

export function breadcrumbJsonLd(
    crumbs: Array<{ name: string; url: string }>,
): JsonLdObject {
    return {
        '@context': 'https://schema.org',
        '@type': 'BreadcrumbList',
        itemListElement: crumbs.map((crumb, index) => ({
            '@type': 'ListItem',
            position: index + 1,
            name: crumb.name,
            item: crumb.url,
        })),
    };
}

export function absoluteUrl(appUrl: string, path: string): string {
    const base = appUrl.replace(/\/$/, '');
    const normalized = path.startsWith('/') ? path : `/${path}`;

    return `${base}${normalized}`;
}
