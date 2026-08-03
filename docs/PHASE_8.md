# Phase 8 — Addons CRUD

## Scope
Sauces, drinks, and extras: price, optional image, type, status, global vs dish-linked.

## Done
- Service: `AddonService` (paginate, CRUD, dish sync, ImageService, toggle)
- Policy: `addons.manage` (admin, manager — **not** assistant)
- Routes: `/admin/addons` resource + `PATCH …/status`
- Pages: index (search/type/scope/status filters), create/edit/show
- Global = all dishes; linked = multi-select dishes via `dish_addon`
- Nav **Catalog → Addons** live
- Demo seed still provides starter addons (`DemoDataSeeder`)

## Tests
`php artisan test --filter=AddonsCrud`

## Exit
- As admin, create a global sauce and a dish-linked extra
- As assistant, confirm 403 on `/admin/addons`
- Next: **Phase 10 — Delivery Zones CRUD**
