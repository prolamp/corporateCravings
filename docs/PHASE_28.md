# Phase 28 — Bulk Orders listing

## Scope
Public bulk orders at `/bulk-orders`: dishes that have active **bulk kg** variations. WhatsApp-only (not cartable). Same dishes as the menu; this page surfaces large trays/deghs.

## Done
- Service: `BulkOrdersService`
- Route: `GET /bulk-orders` (`?category={slug}`)
- Page: category chips + dish cards with kg tier preview, starting Rs. price, link to dish detail for Quote on WhatsApp
- Only active dishes with ≥1 active bulk variation; inactive tiers omitted
- Cards set `cartable: false`

## Tests
`php artisan test --filter=PublicBulkOrders`

## Exit
- Bulk page lists kg dishes; single-only dishes stay on Menu
- Category filter works; unknown slug falls back to all
- Next: **Phase 29 — Cart page** → done; see [PHASE_29.md](./PHASE_29.md)
