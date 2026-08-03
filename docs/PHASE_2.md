# Phase 2 — Admin shell + Dashboard

## Scope
Admin application shell and Dashboard page only. No CRUD modules.

## Done
- **Saffron & Slate theme** (light + dark) from Stitch design tokens in `resources/css/app.css`
- Fonts: **Playfair Display** (display) + **Plus Jakarta Sans** (UI) via Vite Bunny fonts
- **Role-aware sidebar** matching Stitch IA groups (Overview → System); items filtered by Spatie permissions
- Non-Dashboard nav items marked **Soon** until their phases ship
- Top bar: page title, breadcrumbs, **light/dark toggle**, user menu (mobile drawer via existing sidebar Sheet)
- **Dashboard KPIs** via `AdminDashboardService`:
  - Finance (authorized roles): today’s revenue, MTD expenses, estimated profit MTD
  - Ops hub: orders today, pending deliveries, active contracts, customers
  - Today’s subscriber schedule + recent orders tables with StatusBadge labels
- Manager / assistant: **no earnings, P&L, or expense money totals** (matches Stitch locked rules)

## Exit
- `php artisan test --filter=Dashboard`
- Visual check: `/dashboard` as admin (finance) and manager (ops only); theme toggle; sidebar groups
- Next: **Phase 3 — Users & Roles CRUD**
