<?php

namespace App\Console\Commands;

use App\Modules\Subscribers\Services\SubscriberScheduleService;
use Carbon\CarbonInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class RolloverSubscriberSchedulesCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'schedules:rollover-month
                            {--year= : Target year (default: next month)}
                            {--month= : Target month 1-12 (default: next month)}
                            {--contract= : Limit to one subscribe_contracts.id}
                            {--dry-run : Show target month without writing}';

    /**
     * @var string
     */
    protected $description = 'Idempotent monthly subscriber schedule rollover (copies prior month pattern)';

    public function handle(SubscriberScheduleService $schedules): int
    {
        $target = $this->resolveTargetMonth();
        $year = (int) $target->year;
        $month = (int) $target->month;
        $contractId = $this->option('contract') !== null
            ? (int) $this->option('contract')
            : null;

        $this->info(sprintf(
            'Rolling schedules for %s (%04d-%02d)%s',
            $target->format('F Y'),
            $year,
            $month,
            $contractId ? " · contract #{$contractId}" : '',
        ));

        if ($this->option('dry-run')) {
            $this->warn('Dry run — no rows written.');

            return self::SUCCESS;
        }

        $result = $schedules->rolloverActiveContracts($year, $month, $contractId);

        $this->table(
            ['Contract', 'Company', 'Created', 'Skipped', 'Source / Error'],
            collect($result['details'])->map(fn (array $row) => [
                $row['contract_id'],
                $row['company'] ?? '—',
                $row['created'],
                $row['skipped'],
                $row['error'] ?? ($row['source'] ?? '—'),
            ])->all(),
        );

        $this->info(sprintf(
            'Done: %d contract(s), %d created, %d skipped, %d failed.',
            $result['contracts'],
            $result['created'],
            $result['skipped'],
            $result['failed'],
        ));

        return $result['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function resolveTargetMonth(): CarbonInterface
    {
        $year = $this->option('year');
        $month = $this->option('month');

        if ($year !== null && $month !== null) {
            return Carbon::create((int) $year, (int) $month, 1)->startOfDay();
        }

        return now()->startOfMonth()->addMonthNoOverflow();
    }
}
