# Phase 20 — Dish Cost Calculator

## Scope
Stateless **dish** cost estimate: pick **grocery items** (expense rows under the `Groceries` category — Chicken, Rice, Oil…), enter weight/qty used, optional yield → total + cost/portion. Not a category picker. No recipe DB.

## Done
- Service filters to Groceries category items only (latest unit price per title)
- Demo seed: Chicken, Beef, Rice, Oil, Onion, Tomato, etc.
- Page titled **Dish cost calculator**; dropdown shows item + unit price
- Routes: `GET /admin/cost-calculator`, `POST …/estimate`
- Permission: `cost_calculator.view`

## Tests
`php artisan test --filter=DishCostCalculator`

## Exit
- Dropdown lists Chicken / Rice / Oil… not Rent or the word “Groceries” as an option
- Enter weights → estimated dish cost
- Next: **Phase 21 — CMS Pages CRUD** (done — see [PHASE_21.md](./PHASE_21.md))
