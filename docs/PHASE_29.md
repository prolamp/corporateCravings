# Phase 29 — Cart page

## Scope
Session cart for **single-serving (cartable) variations** + optional addons. Cart page review/edit; COD checkout is Phase 30.

## Done
- Service: `CartService` (session storage, live reprice, drop invalid lines)
- Routes: `GET /cart`, `POST/PATCH/DELETE /cart/items`, `DELETE /cart`
- Page: line items (qty +/- remove), order summary (subtotal; delivery “At checkout”), Proceed to Checkout → `/checkout`
- Dish detail **Add to cart** posts to session cart
- Shared Inertia `cart.count` / `cart.quantity` + header badge
- Bulk variations and inactive dishes/addons rejected

## Tests
`php artisan test --filter=PublicCart`

## Exit
- Add from dish detail → cart shows line + total
- Qty update / remove / clear work
- Next: **Phase 30 — COD Checkout page** → done; see [PHASE_30.md](./PHASE_30.md)
