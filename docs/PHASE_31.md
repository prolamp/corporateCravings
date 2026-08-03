# Phase 31 — Order Success + Tracking pages

## Scope
Public order confirmation receipt (from Phase 30) plus `/track-order` lookup with status progress, timeline, items, address, and grand total.

## Done
- Route: `GET /track-order` (throttled `track-order`) with optional `?order=`
- Service: `TrackOrderService` — case-insensitive order number lookup; StatusRegistry labels; progress steps; timeline (no admin notes/actor)
- Page: form + result (progress, history, delivery, items, total only)
- Success page: status badge + track link to `/track-order?order=…`
- Unknown order → ErrorCatalog `order_not_found`
- Cancelled orders show cancelled progress only

## Tests
`php artisan test --filter=PublicTrackOrder`

## Exit
- Success → Track order shows live status
- Nav “Track Order” works for guests with order number
- Next: **Phase 32 — Customer login/dashboard/history/reorder** → done; see [PHASE_32.md](./PHASE_32.md)
