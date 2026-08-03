# Phase 4 — Global Statuses & Error Messages

## Scope
Staff catalog to review and edit **display labels** for domain statuses and **user-facing messages** for error codes. Machine codes are fixed.

## Done
- Admin page: `/admin/statuses` (`admin/statuses/index`)
- Tabs: **Status labels** (by group) and **Error messages**
- Live **StatusBadge** preview while editing labels
- Overrides stored in `settings` (`status_labels` / `error_messages` groups)
- Saving the default text removes the override row
- `StatusRegistry` + `ErrorCatalog` merge overrides on resolve
- Shared Inertia `statuses` props include expense group
- Nav **System → Statuses & Errors** live; gated by `statuses.manage` (admin / super_admin)

## Tests
`php artisan test --filter=StatusErrorCatalog`

## Exit
- As admin, change `order.pending` label and confirm badges/dashboard copy pick it up after refresh
- As manager, confirm 403 on `/admin/statuses`
- Next: **Phase 5 — Categories CRUD**
