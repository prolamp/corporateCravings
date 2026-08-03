# Phase 16 — Manual Bulk/Event Orders CRUD

## Scope
Admin create/edit for **manual** orders (`event` / `individual`, channel `admin`). Line items from dish variations + optional addons. Pricing: list → discount → delivery fee → quoted; optional `amount_paid`. Subscription / schedule-linked orders stay view + status only.

## Done
- Service: `createManual`, `updateManual`, zone/variation/addon option lists; `can_edit` on payloads
- Requests: `StoreManualOrderRequest`, `UpdateManualOrderRequest`
- Routes: full resource except destroy (`create` / `store` / `edit` / `update`)
- Pages: create + edit form (shared `order-form`), **Create order** on index, **Edit** on show when editable
- Permissions: `orders.manage` for create/edit

## Tests
`php artisan test --filter=OrdersCrud`

## Exit
- Admin: Create order → detail with correct totals and timeline
- Admin: Edit manual order (items + pricing)
- Assistant: no Create / Edit (403 on create)
- Subscription orders: Edit forbidden
- Next: **Phase 17 — Expense Categories CRUD** (done — see [PHASE_17.md](./PHASE_17.md))
