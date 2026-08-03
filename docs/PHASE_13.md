# Phase 13 — Subscriber Schedule page

## Scope
Admin month calendar + day list for `subscriber_schedules`: generate from contract meal days, shuffle dishes, edit day, mark delivered (creates subscription order for daily bills).

## Done
- Service: `SubscriberScheduleService` (month payload, generate, shuffle, update, deliver→order, delete)
- Policy: `schedules.manage` (admin, manager, assistant)
- Routes: `GET /admin/schedules`, generate/shuffle POST, update/delete/deliver
- Page: month calendar, day panel, filters, generate/shuffle when a contract is selected
- Deliver sets schedule `delivered` and creates a subscription `Order` + line item (feeds subscriber daily bills)
- Nav **Packages → Schedules** live

## Tests
`php artisan test --filter=SubscriberSchedulesCrud`

## Exit
- Select contract → Generate month → see meal days
- Shuffle meals; edit persons; Deliver → appears on subscriber Daily bills
- Next: **Phase 15 — Unified Orders page**
