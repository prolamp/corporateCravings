# Phase 37 — Legal pages + 404

## Scope
Public legal pages from CMS (Privacy, Terms, Delivery, Cancellation & Refund) and a branded Inertia **404**.

## Done
- Route: `GET /{slug}` constrained to legal slugs → `CmsPageController` → `public/cms-page`
- `CmsPageService`: published-only, HTML sanitize allowlist
- Demo seed: starter legal body copy for each policy page
- Exception renderer: `NotFoundHttpException` → `public/errors/404` (web requests)
- Footer links already pointed at these URLs

## Tests
`php artisan test --filter=PublicLegalPages`

## Exit
- Footer legal links render CMS content when published
- Draft/missing slugs and unknown URLs show branded 404
- Next: **Phase 39 — SEO, sitemap and structured data**
