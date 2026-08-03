# Phase 24 — Public layout + Home page

## Scope
Shared public shell (nav, footer, theme toggle, WhatsApp) and the marketing Home page. Secondary public routes (menu, packages, about, etc.) are linked but land in later phases.

## Done
- Shared Inertia `site` brand payload from `SiteSettingsService::publicPayload()`
- Layout: `PublicLayout` + header / footer / mobile WhatsApp FAB
- Home: full-bleed hero (brand + headline + Browse Menu / WhatsApp), three paths, featured dishes, trust block
- Design images: `docs/stitch/hero image` + `docs/stitch/Built for business, cooked for the soul` → `public/images/home/`
- Route: `GET /` → `HomeController` → `public/home`
- Replaced Laravel welcome stub

## Tests
`php artisan test --filter=PublicHome`

## Exit
- `/` renders branded home with light/dark and mobile nav
- Featured dishes only include `active` catalog items
- Next: **Phase 25 — Menu listing page** → done; see [PHASE_25.md](./PHASE_25.md)
