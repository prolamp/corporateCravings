# Phase 12 — Subscriber Contracts CRUD

## Scope
Admin CRUD for `subscribe_contracts`: private `package_amount`, `discount`, and computed `agreed_total` (per person). Company/contact stay on the subscriber user.

## Done
- Service: `SubscribeContractService` (CRUD, toggle, package history on pricing changes)
- Policy: `contracts.manage` (admin, manager, assistant)
- Routes: `/admin/contracts` resource + `PATCH …/status`
- Pages: index, create, edit, show (private pricing + history)
- `agreed_total` = package_amount − discount (server-side)
- Package history row opened/closed when package or pricing/persons change
- Delete blocked when schedules or orders exist (expire/deactivate instead)
- Nav **Packages → Contracts** live
- Subscriber show **Manage / Add contract** wired

## Tests
`php artisan test --filter=SubscribeContractsCrud`

## Exit
- As admin, create a contract from public package total + discount; confirm agreed total
- Change pricing and see a new history row
- As guest, redirect to login
- Next: **Phase 14 — Monthly Schedule Cron**
