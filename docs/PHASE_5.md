# Phase 5 — Categories CRUD

## Scope
Admin CRUD for menu categories: name (auto slug), image via ImageService, status, sort order. Cannot delete a category that still has dishes.

## Done
- Service: `CategoryService` (paginate, create, update, delete, toggleActive, uniqueSlug)
- Policy: `categories.manage` (admin, manager, assistant)
- Routes: `/admin/categories` resource + `PATCH …/status`
- Pages: index (filters, StatusToggle in actions, pagination), create, edit, show
- Image lifecycle: WebP compress on upload; old file deleted on replace/remove/delete
- Catalog seed uses **Stitch food photos** (`docs/stitch/.../screen.png`) via `CatalogSeeder` (old uploads only as fallback)
- Nav **Catalog → Categories** live

## Tests
`php artisan test --filter=CategoriesCrud`

## Exit
- As admin, create a category with image; confirm slug + WebP storage
- Replace image; confirm old file gone
- Try delete with dishes → blocked; empty category deletes
- Next: **Phase 6 — Dishes CRUD**
