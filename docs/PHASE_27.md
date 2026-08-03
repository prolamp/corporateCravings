# Phase 27 — Corporate Packages listing/detail

## Scope
Public corporate packages at `/packages` and `/packages/{slug}`. Packages are **WhatsApp-only** (not cartable). Show public per-person pricing only — never private contract amounts.

## Done
- Service: `PackagesService` (listing + detail)
- Routes: `GET /packages`, `GET /packages/{slug}`
- Pages: card grid + detail (included dishes, discount strike-through when applicable, Quote on WhatsApp)
- Active packages only; inactive/missing → 404
- Inactive dishes omitted from the included list

## Tests
`php artisan test --filter=PublicPackages`

## Exit
- Listing shows active packages with Rs. per-person pricing
- Detail quotes via WhatsApp; no add-to-cart
- Next: **Phase 28 — Bulk Orders listing** → done; see [PHASE_28.md](./PHASE_28.md)
