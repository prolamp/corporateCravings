# Phase 30 — COD Checkout page

## Scope
Public COD checkout at `/checkout`: customer details, delivery zone fee, place order from session cart. Identity key is **email** (unique). Channel `web`, type `individual`, status `pending`.

## Done
- Services: `CheckoutService` + `OrderService::createWebCheckout`
- Routes: `GET/POST /checkout` (throttled), `GET /orders/{orderNumber}/success`
- Empty cart → redirect to `/cart`
- Zone picker applies fee to total; payment is COD only
- Email exists → attach order to that user (update name/phone/address from checkout; no uniqueness error)
- New email → create customer (password = phone) + silent auto-login (session only)
- Auto-login never redirects to dashboard — flow stays checkout → order success
- Already logged in → stay logged in (no re-login)
- Cart cleared after successful place
- Order success shows receipt: line items, qty, addons, address, payment, grand total
- Tracking expanded in Phase 31 — see [PHASE_31.md](./PHASE_31.md)

## Tests
`php artisan test --filter=PublicCheckout`

## Exit
- Cart → checkout → place order → success with order number
- Order appears in admin with channel Website
- Next: **Phase 31 — Order Success + Tracking pages** → done; see [PHASE_31.md](./PHASE_31.md)
