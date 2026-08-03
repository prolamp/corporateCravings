# Phase 14 — Monthly Schedule Cron

## Scope
Idempotent monthly rollover of `subscriber_schedules` for active contracts. Prefer the **previous month’s dish + persons pattern**; fall back to package dish rotation when no prior month exists. Unique `(subscribe_contract_id, date)` prevents duplicates.

## Done
- Service: `SubscriberScheduleService::rolloverMonth` / `rolloverActiveContracts`
- Artisan: `php artisan schedules:rollover-month [--year=] [--month=] [--contract=] [--dry-run]`
- Scheduler: runs **monthly on the 1st at 01:00** (`routes/console.php`)
- Default target month = **next calendar month**
- Re-runs skip existing days (`skipped` count)

## Ops
```bash
# Preview target (no writes)
php artisan schedules:rollover-month --dry-run

# Roll August 2026 for one contract
php artisan schedules:rollover-month --year=2026 --month=8 --contract=1

# Ensure cron hits Laravel scheduler
* * * * * cd /var/www/cravings-new && php artisan schedule:run >> /dev/null 2>&1
```

## Tests
`php artisan test --filter=ScheduleRollover`

## Exit
- Run rollover twice for the same month → same day count, no duplicates
- With a prior month seeded, new month copies dish/persons sequence
- Next: **Phase 16 — Manual Bulk/Event Orders CRUD**
