# Phase 21 — CMS Pages CRUD

## Scope
Admin CRUD for **text-only** site pages (Privacy, Terms, Delivery, Cancellation/Refund, FAQ): title, slug, body, meta, draft/published. **About** is not a CMS page — it ships later as a Stitch-designed public page (Phase 34).

## Done
- Service: `PageService` (paginate, CRUD, toggle draft/published, unique slug)
- Policy: `cms.manage` (admin, manager — not assistant)
- Status group: `page` (`draft` / `published`)
- Routes: `/admin/pages` resource + `PATCH …/status`
- Pages: index, create, edit, show
- Demo seed: policy + FAQ pages only (no About)
- Nav **Content → Pages** live

## Tests
`php artisan test --filter=CmsPagesCrud`

## Exit
- Create/edit a text page; publish/unpublish; delete
- Assistant: 403
- Next: **Phase 22 — Site Settings page** (done — see [PHASE_22.md](./PHASE_22.md))
