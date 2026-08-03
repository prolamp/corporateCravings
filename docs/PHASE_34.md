# Phase 34 — About page

## Scope
Public marketing **About** page (Stitch-designed surface — **not** a CMS page). Nav/footer already linked here from Phase 24.

## Done
- Route: `GET /about` → `AboutController` → `public/about`
- Service: `AboutService` (Stitch About copy + `public/images/about/` assets from `docs/stitch/about/`)
- Sections: legacy hero, Saffron & Slate story, Built for business, Preferred partner stats, values, CTA
- CTAs: Menu, Packages, Bulk, WhatsApp

## Tests
`php artisan test --filter=PublicAbout`

## Exit
- `/about` renders branded About with light/dark and mobile layout
- Next: **Phase 36 — FAQ page**
