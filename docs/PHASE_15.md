# Phase 15 — Unified Orders page

## Scope
Admin list + detail for **all** order types/channels (individual, event, subscription). Filters, line items, read-only amount paid, status updates with timeline. No create/edit of bulk orders (Phase 16).

## Done
- Service: `OrderService` (paginate, toArray, updateStatus + history, WhatsApp link)
- Policy: `orders.view` / `orders.update_status` / `orders.manage`
- Routes: `GET /admin/orders`, `GET /admin/orders/{order}`, `PATCH …/status`
- Pages: index (filters + clear), show (items, totals, timeline, status form)
- Schedule deliver writes an initial status history row
- Nav **Orders → Orders** live

## Tests
`php artisan test --filter=OrdersCrud`

## Exit
- List mixed orders; filter by type/status
- Open detail; change status → timeline entry
- As assistant: view + status OK; `canManage` false
- Next: **Phase 16 — Manual Bulk/Event Orders CRUD** (done — see [PHASE_16.md](./PHASE_16.md))
