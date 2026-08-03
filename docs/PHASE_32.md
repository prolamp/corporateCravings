# Phase 32 — Customer login/dashboard/history/reorder

## Scope
Customer portal: login lands on customer home; dashboard with stats + recent orders; order history; reorder into session cart.

## Done
- Login/Register responses redirect by role (`user_dashboard_route`) — customers → `/customer/dashboard`
- Registration creates `customer` role users
- Checkout-created customers get `email_verified_at`
- Routes: `GET /customer/dashboard`, `GET /customer/orders`, `POST /customer/orders/{order}/reorder`
- Service: `CustomerPortalService` (stats, history, reorder with skip of unavailable lines)
- Pages use public layout (not admin shell)
- Reorder → cart with success/partial flash via ErrorCatalog

## Tests
`php artisan test --filter=CustomerPortal`

## Exit
- Login as customer → account dashboard
- History lists own orders; reorder fills cart
- Other roles forbidden from customer portal
- Next: **Phase 33 — Subscriber portal** → done; see [PHASE_33.md](./PHASE_33.md)
