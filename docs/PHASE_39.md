# Phase 39 — SEO, sitemap and structured data

## Scope
Public crawlability: meta/OG tags, `robots.txt`, `sitemap.xml`, and JSON-LD on key pages.

## Done
- Shared Inertia `seo` defaults (site name, description, default image, `app_url`, Organization schema)
- `SeoHead` on public pages: title suffix, description, canonical, Open Graph, Twitter, robots
- `noindex` on cart, checkout, order success, track order, and 404
- JSON-LD: Organization/FoodEstablishment (home/about), FAQPage, Product + BreadcrumbList (dishes/packages)
- Routes: `GET /robots.txt`, `GET /sitemap.xml` (static pages + active dishes/packages + published legal pages)
- Removed static `public/robots.txt` so the dynamic route is used

## Tests
`php artisan test --filter=PublicSeo`

## Exit
- Sitemap lists public catalog URLs; robots points at it and blocks private areas
- Indexable pages expose meta + structured data; transactional pages are noindex
- Public website phases complete — next is later **Mobile API (Sanctum)** when needed
