# Phase 11 — Subscribers CRUD

## Scope
Admin CRUD for **subscriber company accounts** (users with role `subscriber`). Contact/company live on `users`. Full contract CRUD is Phase 12.

## Done
- Service: `SubscriberService` (list role=subscriber, create/update/delete via UserService, read-only contract summary)
- Routes: `/admin/subscribers` resource + `PATCH …/status`
- Pages: index (company, package, staff-only agreed total), create/edit (company + contact), show (identity + contract summary)
- Permissions: list via `contracts.manage`; create via `users.create_subscriber`; update/delete via existing user perms
- Delete blocked when contracts exist
- Nav **Packages → Subscribers** live
- “Manage contract” CTA links to Phase 12 contracts

## Tests
`php artisan test --filter=SubscribersCrud`

## Exit
- As admin, create a subscriber company and view demo contract summary
- As assistant, list OK / create 403
- Next: **Phase 12 — Subscriber Contracts CRUD**
