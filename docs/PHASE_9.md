# Phase 9 — Packages CRUD

## Scope
Corporate meal packages: dishes → avg `base_amount_per_person` (sum ÷ dish count) → admin discount → public `total_amount_per_person`.

## Done
- Service: `PackageService` (paginate, CRUD, item sync, pricing, ImageService, toggle)
- Pricing: average of single-serving snapshots → apply `discount_amount` + `discount_percent` → clamp total ≥ 0
- Policy: `packages.manage` (admin, manager — not assistant)
- Routes: `/admin/packages` resource + `PATCH …/status`
- Pages: index (search/status), create/edit (dish picker + order + live pricing), show
- Delete blocked when subscriber contracts exist
- Nav **Packages → Packages** live

## Tests
`php artisan test --filter=PackagesCrud`

## Exit
- Create a package with 2+ dishes and a discount; confirm public total
- As assistant, confirm 403
- Next: **Phase 11 — Subscribers CRUD**
