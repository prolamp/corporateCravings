# Phase 10 — Delivery Zones CRUD

## Scope
Admin CRUD for delivery areas: name, PKR fee, active flag, sort order. Checkout will apply the selected zone fee; orders store a fee snapshot.

## Done
- Service: `DeliveryZoneService` (paginate, CRUD, toggle `is_active`)
- Policy: `delivery_zones.manage` (admin, manager — not assistant)
- Routes: `/admin/delivery-zones` resource + `PATCH …/status`
- Pages: index (search/status), create, edit, show
- Delete blocked when the zone is used on orders (deactivate instead)
- Nav **Operations → Delivery Zones** live
- Demo seed still provides Islamabad sectors

## Tests
`php artisan test --filter=DeliveryZonesCrud`

## Exit
- As admin, create/edit a zone fee and toggle active
- As assistant, confirm 403
- Next: **Phase 13 — Subscriber Schedule page**
