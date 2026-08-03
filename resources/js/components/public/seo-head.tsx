import { Head, usePage } from '@inertiajs/react';

export type JsonLdObject = Record<string, unknown>;

type Props = {
    title: string;
    description?: string | null;
    image?: string | null;
    /** Path for canonical, e.g. /menu/biryani — defaults to current Inertia URL (no query). */
    path?: string | null;
    noindex?: boolean;
    type?: 'website' | 'article' | 'product';
    jsonLd?: JsonLdObject | JsonLdObject[] | null;
    /** When true, do not append the brand title suffix. */
    rawTitle?: boolean;
};

function toAbsolute(
    value: string | null | undefined,
    appUrl: string,
): string | null {
    if (!value) {
        return null;
    }
    if (value.startsWith('http://') || value.startsWith('https://')) {
        return value;
    }
    const base = appUrl.replace(/\/$/, '');
    const path = value.startsWith('/') ? value : `/${value}`;

    return `${base}${path}`;
}

export function SeoHead({
    title,
    description,
    image,
    path,
    noindex = false,
    type = 'website',
    jsonLd = null,
    rawTitle = false,
}: Props) {
    const page = usePage();
    const seo = page.props.seo;
    const siteName = seo?.site_name ?? 'Corporate Cravings';
    const appUrl = seo?.app_url ?? '';
    const suffix = seo?.title_suffix ?? ` | ${siteName}`;
    const fullTitle =
        rawTitle || title.includes(siteName)
            ? title
            : `${title}${suffix}`;
    const metaDescription =
        description?.trim() ||
        seo?.default_description ||
        'Fresh Pakistani meals for Islamabad offices and events.';

    const fromUrl = page.url.split('?')[0] || '/';
    const pathOnly = (path ?? fromUrl).trim() || '/';
    const canonical = toAbsolute(pathOnly, appUrl) ?? pathOnly;
    const imageUrl =
        toAbsolute(image, appUrl) || seo?.default_image || null;

    const graphs = jsonLd ? (Array.isArray(jsonLd) ? jsonLd : [jsonLd]) : [];

    return (
        <Head title={fullTitle}>
            <meta
                head-key="description"
                name="description"
                content={metaDescription}
            />
            <meta
                head-key="robots"
                name="robots"
                content={noindex ? 'noindex, nofollow' : 'index, follow'}
            />
            <link head-key="canonical" rel="canonical" href={canonical} />

            <meta head-key="og:type" property="og:type" content={type} />
            <meta
                head-key="og:site_name"
                property="og:site_name"
                content={siteName}
            />
            <meta head-key="og:title" property="og:title" content={fullTitle} />
            <meta
                head-key="og:description"
                property="og:description"
                content={metaDescription}
            />
            <meta head-key="og:url" property="og:url" content={canonical} />
            {imageUrl ? (
                <meta
                    head-key="og:image"
                    property="og:image"
                    content={imageUrl}
                />
            ) : null}
            <meta
                head-key="og:locale"
                property="og:locale"
                content={seo?.locale ?? 'en_PK'}
            />

            <meta
                head-key="twitter:card"
                name="twitter:card"
                content="summary_large_image"
            />
            <meta
                head-key="twitter:title"
                name="twitter:title"
                content={fullTitle}
            />
            <meta
                head-key="twitter:description"
                name="twitter:description"
                content={metaDescription}
            />
            {imageUrl ? (
                <meta
                    head-key="twitter:image"
                    name="twitter:image"
                    content={imageUrl}
                />
            ) : null}

            {graphs.map((graph, index) => (
                <script
                    key={`jsonld-${index}`}
                    type="application/ld+json"
                    dangerouslySetInnerHTML={{
                        __html: JSON.stringify(graph),
                    }}
                />
            ))}
        </Head>
    );
}
