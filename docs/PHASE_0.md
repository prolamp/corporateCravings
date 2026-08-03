# Corporate Cravings — Phase 0 notes

## Stack
- Laravel 13 + Inertia React + MySQL (`corporate_craving`)
- Spatie Permission, Sanctum, Intervention Image

## Conventions
- Modules live under `app/Modules/{Name}/`
- Shared helpers under `app/Support/`
- All images via `ImageService` (WebP/JPG compress, replace deletes old, delete removes file)
- Statuses via `config/statuses.php` + `StatusRegistry`
- Errors via `config/errors.php` + `ErrorCatalog`
- API/JSON shape: `{ success, message, code, data, errors, meta }`

## Roles
`super_admin`, `admin`, `manager`, `assistant`, `subscriber`, `customer`
