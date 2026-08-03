# Phase 1 — Fresh database schema & seed

## Principle
**Data only from the old project** (dish/category names, descriptions, image files).  
**Zero reuse of old DB flow** — no Prisma models, cuid IDs, multi-flag variation columns, recipe/raw-material inventory, or alter-stack migrations.

Schema is purpose-built for Laravel 13 + MySQL: clear FKs, soft deletes, decimal money, indexed status/channel/date columns, private contract pricing, package public totals, ImageService lifecycle.

## Migration style
All domain tables are **create** migrations only. Profile + 2FA live on the base `users` create — no additive alter migrations for fields we already know we need.

## Tables (fresh)

| Area | Tables |
|------|--------|
| Auth | `users` (phone, company, image, status, 2FA), sessions, password resets, passkeys, sanctum tokens, Spatie roles/permissions |
| Catalog | `categories`, `dishes` (one `image`), `dish_variations` (`type`: single\|bulk) |
| Addons | `addons`, `dish_addon` |
| Packages | `packages`, `package_items` |
| Delivery | `delivery_zones` |
| Subscribers | `subscribe_contracts`, `subscriber_package_history`, `subscriber_schedules` |
| Orders | `orders`, `order_items`, `order_item_addons`, `order_status_histories` |
| Finance | `expense_categories`, `expenses` (grocery unit prices feed the calculator) |
| CMS | `settings`, `pages` |

## Explicitly NOT in schema
- `raw_materials`, `recipe_ingredients`, `dish_recipes`, `dish_cost_estimates`
- `dishes.images` gallery column
- `dish_variations.is_single` / `is_cartable` / `is_bulk` / `on_listing`
- `expenses.raw_material_id`
- Old `subscribers` table (contact lives on `users`; contracts are `subscribe_contracts`)

## Dish cost calculator
Stateless UI: pick grocery expenses → enter quantities → enter yield → `DishCostCalculator` returns total + cost/portion. Nothing extra is stored.

## Seed (after `migrate:fresh --seed`)
- Roles/permissions
- Catalog names + images via `ImageService` (WebP)
- Demo package (`total_amount_per_person` public)
- Demo subscriber contract (private `package_amount` / `discount` / `agreed_total`)
- Schedules, zones, addons, grocery expense category, CMS pages

## Hardening (security + performance)
- Listing indexes: `(status, sort_order)` on catalog/packages/addons/zones/pages
- Order track/lookup: `(customer_phone, status)`, `(user_id, status)`, `(subscribe_contract_id, status)`
- Grocery calculator: `(expense_category_id, title)`
- MySQL CHECK constraints on money ≥ 0, enum-like `type`/`channel`/`unit`/`meal_type`, quantities ≥ 1
- `orders.amount_paid` removed from mass assignment (admin `forceFill` only)
- Eloquent graph complete: User↔Orders/Contracts, Package↔Dishes/Contracts, Dish single/bulk variations, Zone↔Orders
