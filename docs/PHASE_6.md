# Phase 6 — Dishes CRUD

## Scope
Admin CRUD for dishes: unique title (auto slug), category, short description, one image via ImageService, status, sort order, and **nested portion variations** (label, qty, unit, price, type single|bulk, status).

## Done
- Service: `DishService` (paginate, create/update with variation sync, delete, toggleActive)
- Policy: `dishes.manage` (admin, manager, assistant)
- Routes: `/admin/dishes` resource + `PATCH …/status`
- Pages: index (search/category/status filters, StatusToggle, pagination), create/edit (Stitch-style form + variations table), show
- Units: `serving` | `kg` | `persons` · Types: single (cartable) / bulk (WhatsApp)
- Image lifecycle: WebP compress; old file removed on replace/remove/delete
- **Catalog seed** uses Stitch food photos (`docs/stitch/.../screen.png`) for dishes + categories
- Nav **Catalog → Dishes** live

## Tests
`php artisan test --filter=DishesCrud`

## Exit
- As admin, open `/admin/dishes` — seeded dishes show Stitch images
- Create/edit a dish with single + bulk variations
- Replace image; confirm old file gone
- Next: **Phase 9 — Packages CRUD**
