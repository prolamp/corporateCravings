# Phase 19 — Expenses CRUD

## Scope
Admin expenses list/create/edit/show: category, title, qty × unit price → total, optional unit (groceries), date, notes, status (`pending` / `approved` / `rejected`), optional receipt image (ImageService WebP). Filters + clear. Aggregate filtered totals only for roles with `reports.earnings` (admin). Assistants can create; manage (edit/delete/status) requires `expenses.manage`.

## Done
- Service: `ExpenseService` (paginate, filtered total, CRUD, status, receipt lifecycle)
- Policy: `expenses.view` / `create` / `manage`
- Routes: `/admin/expenses` resource + `PATCH …/status`
- Pages: index, create, edit, show
- Nav **Operations → Expenses** live

## Tests
`php artisan test --filter=ExpensesCrud`

## Exit
- Assistant: create expense; no edit / no filtered totals
- Admin: create/edit/delete, status update, see filtered total
- Manager: manage expenses, no aggregate totals
- Next: **Phase 20 — Dish Cost Calculator** (done — see [PHASE_20.md](./PHASE_20.md))
