# Phase 17 — Expense Categories CRUD

## Scope
Admin CRUD for expense categories: name (unique), optional description, active flag. Categories group expenses for later Expenses CRUD and the dish cost calculator. Delete blocked when expenses exist (deactivate instead).

## Done
- Service: `ExpenseCategoryService` (paginate, CRUD, toggle `is_active`)
- Policy: `expenses.manage` (admin, manager — not assistant)
- Routes: `/admin/expense-categories` resource + `PATCH …/status`
- Pages: index (search/status), create, edit, show
- Nav **Operations → Expense Categories** live; Expenses stays coming soon (Phase 19)

## Tests
`php artisan test --filter=ExpenseCategoriesCrud`

## Exit
- As admin, create/edit a category and toggle active
- Delete unused category; used category blocked with error
- As assistant, confirm 403
- Next: **Phase 18 skipped** → **Phase 19 — Expenses CRUD**
