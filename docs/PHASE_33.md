# Phase 33 — Subscriber portal

## Scope
Subscriber account portal: dashboard (delivered stats + upcoming meals), monthly schedules, delivery history, and private contract pricing (visible only to that subscriber).

## Done
- Routes: `GET /subscriber/dashboard|schedules|history|contract`
- Service: `SubscriberPortalService`
- Pages use **admin sidebar shell** (Dashboard, Schedules, History, Contract) — not the public customer layout
- Contract page shows private `package_amount` / `discount` / `agreed_total` (not public marketing rates)
- Schedules filterable by `?month=YYYY-MM`
- History: delivered schedule rows only
- Login already role-routes subscribers here (Phase 32)

## Tests
`php artisan test --filter=SubscriberPortal`

## Exit
- Subscriber login → portal dashboard
- Schedules / history / contract work; customers forbidden
- Next: **Phase 33.5 — Subscriber bill slips / receipts** (then Phase 34 About)
