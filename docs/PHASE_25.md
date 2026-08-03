# Phase 25 — Menu listing page

## Scope
Public menu of **single-serving** dishes with category filter chips. No bulk kg tiers on this page (those belong to Bulk Orders).

## Done
- Service: `MenuService` + shared `PublicDishPresenter`
- Route: `GET /menu` (`?category={slug}`)
- Page: chips + dish grid (image, title, short description, “Starts at Rs. …”)
- Cards link to `/menu/{slug}` (Phase 26)
- Next: **Phase 26 — Dish detail page** → done; see [PHASE_26.md](./PHASE_26.md)
