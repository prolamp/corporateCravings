# Phase 26 — Dish detail page

## Scope
Public dish detail at `/menu/{slug}` with single-serving order UI (variation, qty, addons, Add to cart) and a separate bulk section (kg tiers + Quote on WhatsApp only).

## Done
- Service: `DishDetailService`
- Route: `GET /menu/{slug}` → `public/menu/show`
- Active dish only (inactive/missing → 404)
- Active single + bulk variations; active global and linked addons
- Bulk never marked cartable; WhatsApp quote uses shared `site.whatsapp_url`
- Add to cart UI computes line total (session cart/checkout in Phase 29)

## Tests
`php artisan test --filter=PublicDishDetail`

## Exit
- Menu cards open detail
- Single section supports selection + addons; bulk is WhatsApp-only
- Next: **Phase 27 — Corporate Packages listing/detail** → done; see [PHASE_27.md](./PHASE_27.md)
