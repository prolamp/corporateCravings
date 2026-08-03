<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
| Monthly subscriber schedule rollover — 1st of each month at 01:00.
| Idempotent: unique (contract, date) prevents duplicates on re-runs.
| Manual: php artisan schedules:rollover-month [--year=] [--month=] [--contract=]
*/
Schedule::command('schedules:rollover-month')
    ->monthlyOn(1, '01:00')
    ->withoutOverlapping()
    ->name('schedules-rollover-month');
