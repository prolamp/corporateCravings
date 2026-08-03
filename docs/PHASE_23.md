# Phase 23 — Reports pages

## Scope
Permission-gated admin reports hub with Orders, Contracts, Earnings, and Profit & loss. Date-range filters on volume/finance reports. Managers see ops reports only; finance reports are admin-only.

## Done
- Service: `App\Modules\Reports\Services\ReportService`
- Controller/routes under `/admin/reports` (+ orders, contracts, earnings, profit-loss)
- Permissions: `reports.orders`, `reports.contracts`, `reports.earnings`, `reports.profit_loss`
- Nav **Reports → Reports** (visible when `reports.orders` is granted)
- Pages: hub + four report views with summaries and breakdowns

## Tests
`php artisan test --filter=Reports`

## Exit
- Admin sees all four reports; manager sees Orders + Contracts; assistant 403
- Date filters default to current month
- Next: **Phase 24 — Public layout + Home page**
