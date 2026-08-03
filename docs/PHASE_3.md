# Phase 3 — Users & Roles CRUD

## Scope
Admin Users module only (list / create / edit / show / delete). Role assignment respects creatable-role permissions.

## Done
- Module: `app/Modules/Users/` — `UserService`, `UserController`, Form Requests, `UserPolicy`
- Routes: `admin/users` resource (auth + verified)
- Pages: `resources/js/pages/admin/users/{index,create,edit,show}.tsx`
- Sidebar **People → Users** live (no longer “Soon”)
- Image uploads via `ImageService` → `users/` (WebP, replace/delete)
- Password optional on create (defaults to phone); optional on update
- Role rules locked:
  - Super admin → can create admin
  - Admin → managers/assistants/subscribers/customers (not admins)
  - Manager → assistants/subscribers/customers (not managers)
  - Assistant → view only (no create)
- Cannot delete self; cannot manage users above your creatable set
- **List visibility:** excludes yourself and any user with a **higher** role (e.g. admin never sees super admins); role filter dropdown matches the same ceiling
- Direct show/edit URLs for above-level users are forbidden

## Tests
`php artisan test --filter=UsersCrud` (11 passing)

## Exit
- Login as super admin → `/admin/users` → create an admin
- Login as admin → confirm admin role is not in the create dropdown
- Next: **Phase 4 — Global Statuses & Error Messages page**
