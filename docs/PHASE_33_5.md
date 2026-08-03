# Phase 33.5 — Subscriber bill slips / receipts

## Scope
Admin generates bill slips from **delivered** subscription schedule days. Slips are downloadable receipts; marking **paid** tags those schedule days and sets linked orders’ `amount_paid` so dashboard/reports Paid totals stay accurate (no double-count).

## Data
- `subscriber_bill_slips` + `subscriber_bill_slip_items` (line snapshots)
- `subscriber_schedules.payment_status` (`unpaid`|`paid`) + `subscriber_bill_slip_id`
- Statuses: `bill_slip` (`draft`|`issued`|`paid`|`cancelled`) and `payment` (`unpaid`|`paid`)
- Permissions: `bill_slips.view`, `bill_slips.manage`, `portal.subscriber.bills`

## Domain
`BillSlipService`: eligible deliveries, create/update, mark paid/unpaid, cancel, receipt payload, admin + subscriber lists.

## Admin UI
- Sidebar **Bill slips** → `/admin/bill-slips`
- Create from contract + multi-select delivered days (+ adjustment)
- Entry points: subscriber show, contract show, Reports index
- Schedules calendar: Paid/Unpaid payment badges
- Receipt: print-friendly HTML (`/admin/bill-slips/{id}/receipt`)

## Subscriber portal
- **Bills** → `/subscriber/bills` (+ show + receipt)
- Schedules/history show Paid/Unpaid badges
- Schedules load **all** user contracts (not only active); default month prefers nearest month with entries

## Paid totals
Marking a slip paid sets each linked order’s `amount_paid` to that day’s line total. Existing ReportService / AdminDashboardService sums of `orders.amount_paid` pick this up without double-counting slip totals separately.

## Tests
`php artisan test --filter=BillSlips`

## Exit
- Admin can generate, download, and mark slips paid
- Paid days show green Paid badge; unpaid red Unpaid
- Subscriber can view/download own slips only
- Next: **Phase 35 — Contact page**
