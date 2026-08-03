<?php

namespace App\Modules\Subscribers\Services;

use App\Modules\Catalog\Models\Dish;
use App\Modules\Orders\Models\Order;
use App\Modules\Orders\Models\OrderItem;
use App\Modules\Orders\Models\OrderStatusHistory;
use App\Modules\Packages\Models\Package;
use App\Modules\Subscribers\Models\SubscribeContract;
use App\Modules\Subscribers\Models\SubscriberSchedule;
use App\Support\Enums\OrderChannel;
use App\Support\Enums\OrderType;
use App\Support\Money;
use App\Support\StatusRegistry;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class SubscriberScheduleService
{
    private const DAY_KEYS = [
        0 => 'sun',
        1 => 'mon',
        2 => 'tue',
        3 => 'wed',
        4 => 'thu',
        5 => 'fri',
        6 => 'sat',
    ];

    /**
     * @param  array{
     *     year?: int|null,
     *     month?: int|null,
     *     contract_id?: int|null,
     *     status?: string|null,
     *     search?: string|null,
     *     view?: string|null,
     * }  $filters
     * @return array<string, mixed>
     */
    public function monthPayload(array $filters = []): array
    {
        $year = (int) ($filters['year'] ?? now()->year);
        $month = (int) ($filters['month'] ?? now()->month);
        $start = Carbon::create($year, $month, 1)->startOfDay();
        $end = $start->copy()->endOfMonth();

        $query = SubscriberSchedule::query()
            ->with([
                'contract.user:id,name,company_name',
                'contract.package:id,title',
                'package:id,title',
                'dish:id,title',
                'order:id,subscriber_schedule_id,order_number,status',
            ])
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('date')
            ->orderBy('id');

        if (! empty($filters['contract_id'])) {
            $query->where('subscribe_contract_id', (int) $filters['contract_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($q) use ($search): void {
                $q->where('dish_title', 'like', "%{$search}%")
                    ->orWhere('package_title', 'like', "%{$search}%")
                    ->orWhereHas('contract.user', function ($user) use ($search): void {
                        $user->where('company_name', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });
            });
        }

        $schedules = $query->get();

        $days = [];
        for ($day = 1; $day <= $end->day; $day++) {
            $date = $start->copy()->day($day);
            $days[] = [
                'date' => $date->toDateString(),
                'day' => $date->day,
                'weekday' => $date->format('D'),
                'is_today' => $date->isToday(),
                'is_weekend' => $date->isWeekend(),
                'entries' => $schedules
                    ->filter(fn (SubscriberSchedule $s) => $s->date?->toDateString() === $date->toDateString())
                    ->map(fn (SubscriberSchedule $s) => $this->toArray($s))
                    ->values()
                    ->all(),
            ];
        }

        return [
            'year' => $year,
            'month' => $month,
            'month_label' => $start->format('F Y'),
            'start' => $start->toDateString(),
            'end' => $end->toDateString(),
            'days' => $days,
            'entries' => $schedules->map(fn (SubscriberSchedule $s) => $this->toArray($s))->values()->all(),
            'counts' => [
                'total' => $schedules->count(),
                'scheduled' => $schedules->where('status', 'scheduled')->count(),
                'confirmed' => $schedules->where('status', 'confirmed')->count(),
                'delivered' => $schedules->where('status', 'delivered')->count(),
            ],
        ];
    }

    /**
     * Idempotent: creates missing meal days for the contract month from days_of_week.
     *
     * @return array{created: int, skipped: int}
     */
    public function generateMonth(SubscribeContract $contract, int $year, int $month): array
    {
        if ($contract->status !== 'active') {
            throw new InvalidArgumentException('Only active contracts can generate schedules.');
        }

        $package = $contract->package()->with(['items.dish'])->first();
        if (! $package) {
            throw new InvalidArgumentException('Contract has no package.');
        }

        $dishes = $this->packageDishes($package);
        if ($dishes->isEmpty()) {
            throw new InvalidArgumentException('Package has no dishes to schedule.');
        }

        $mealDays = array_values(array_unique($contract->days_of_week ?? []));
        if ($mealDays === []) {
            throw new InvalidArgumentException('Contract has no meal days configured.');
        }

        $start = Carbon::create($year, $month, 1)->startOfDay();
        $end = $start->copy()->endOfMonth();
        $created = 0;
        $skipped = 0;
        $dishIndex = 0;

        return DB::transaction(function () use (
            $contract,
            $package,
            $dishes,
            $mealDays,
            $start,
            $end,
            &$created,
            &$skipped,
            &$dishIndex,
        ): array {
            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                $key = self::DAY_KEYS[$date->dayOfWeek] ?? null;
                if ($key === null || ! in_array($key, $mealDays, true)) {
                    continue;
                }

                if ($contract->starts_on && $date->lt($contract->starts_on->startOfDay())) {
                    continue;
                }

                if ($contract->ends_on && $date->gt($contract->ends_on->endOfDay())) {
                    continue;
                }

                $exists = SubscriberSchedule::query()
                    ->where('subscribe_contract_id', $contract->id)
                    ->whereDate('date', $date->toDateString())
                    ->exists();

                if ($exists) {
                    $skipped++;

                    continue;
                }

                $dish = $dishes[$dishIndex % $dishes->count()];
                $dishIndex++;

                $this->createDay($contract, $package, $dish, $date);
                $created++;
            }

            return ['created' => $created, 'skipped' => $skipped];
        });
    }

    /**
     * Roll a target month for one contract using the previous month's meal pattern
     * (dish + persons sequence). Falls back to package rotation when no prior month.
     * Idempotent: existing (contract, date) rows are never duplicated.
     *
     * @return array{created: int, skipped: int, source: string}
     */
    public function rolloverMonth(SubscribeContract $contract, int $year, int $month): array
    {
        if ($contract->status !== 'active') {
            throw new InvalidArgumentException('Only active contracts can roll over schedules.');
        }

        $package = $contract->package()->with(['items.dish'])->first();
        if (! $package) {
            throw new InvalidArgumentException('Contract has no package.');
        }

        $targetStart = Carbon::create($year, $month, 1)->startOfDay();
        $targetEnd = $targetStart->copy()->endOfMonth();
        $prevStart = $targetStart->copy()->subMonthNoOverflow()->startOfMonth();
        $prevEnd = $prevStart->copy()->endOfMonth();

        $pattern = SubscriberSchedule::query()
            ->where('subscribe_contract_id', $contract->id)
            ->whereBetween('date', [$prevStart->toDateString(), $prevEnd->toDateString()])
            ->whereNotIn('status', ['cancelled', 'skipped'])
            ->orderBy('date')
            ->get(['dish_id', 'dish_title', 'persons', 'package_id', 'package_title']);

        $source = $pattern->isNotEmpty() ? 'previous_month' : 'package_rotation';

        if ($pattern->isEmpty()) {
            $result = $this->generateMonth($contract, $year, $month);

            return [
                'created' => $result['created'],
                'skipped' => $result['skipped'],
                'source' => $source,
            ];
        }

        $mealDays = array_values(array_unique($contract->days_of_week ?? []));
        if ($mealDays === []) {
            throw new InvalidArgumentException('Contract has no meal days configured.');
        }

        $created = 0;
        $skipped = 0;
        $patternIndex = 0;

        return DB::transaction(function () use (
            $contract,
            $package,
            $pattern,
            $mealDays,
            $targetStart,
            $targetEnd,
            $source,
            &$created,
            &$skipped,
            &$patternIndex,
        ): array {
            for ($date = $targetStart->copy(); $date->lte($targetEnd); $date->addDay()) {
                $key = self::DAY_KEYS[$date->dayOfWeek] ?? null;
                if ($key === null || ! in_array($key, $mealDays, true)) {
                    continue;
                }

                if ($contract->starts_on && $date->lt($contract->starts_on->startOfDay())) {
                    continue;
                }

                if ($contract->ends_on && $date->gt($contract->ends_on->endOfDay())) {
                    continue;
                }

                $exists = SubscriberSchedule::query()
                    ->where('subscribe_contract_id', $contract->id)
                    ->whereDate('date', $date->toDateString())
                    ->exists();

                if ($exists) {
                    $skipped++;

                    continue;
                }

                /** @var SubscriberSchedule $template */
                $template = $pattern[$patternIndex % $pattern->count()];
                $patternIndex++;

                $this->createDayFromTemplate($contract, $package, $template, $date);
                $created++;
            }

            return [
                'created' => $created,
                'skipped' => $skipped,
                'source' => $source,
            ];
        });
    }

    /**
     * Roll every active contract for the given month.
     *
     * @return array{
     *     contracts: int,
     *     created: int,
     *     skipped: int,
     *     failed: int,
     *     details: list<array{contract_id: int, company: string|null, created: int, skipped: int, source?: string, error?: string}>
     * }
     */
    public function rolloverActiveContracts(int $year, int $month, ?int $contractId = null): array
    {
        $query = SubscribeContract::query()
            ->with(['user:id,name,company_name', 'package'])
            ->where('status', 'active')
            ->orderBy('id');

        if ($contractId) {
            $query->whereKey($contractId);
        }

        $contracts = $query->get();
        $created = 0;
        $skipped = 0;
        $failed = 0;
        $details = [];

        foreach ($contracts as $contract) {
            $company = $contract->user?->company_name ?: $contract->user?->name;

            try {
                $result = $this->rolloverMonth($contract, $year, $month);
                $created += $result['created'];
                $skipped += $result['skipped'];
                $details[] = [
                    'contract_id' => $contract->id,
                    'company' => $company,
                    'created' => $result['created'],
                    'skipped' => $result['skipped'],
                    'source' => $result['source'],
                ];
            } catch (InvalidArgumentException|\Throwable $e) {
                $failed++;
                $details[] = [
                    'contract_id' => $contract->id,
                    'company' => $company,
                    'created' => 0,
                    'skipped' => 0,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'contracts' => $contracts->count(),
            'created' => $created,
            'skipped' => $skipped,
            'failed' => $failed,
            'details' => $details,
        ];
    }

    /**
     * Re-assign package dishes on non-delivered days in the month.
     *
     * @return array{updated: int}
     */
    public function shuffleMonth(SubscribeContract $contract, int $year, int $month): array
    {
        $package = $contract->package()->with(['items.dish'])->first();
        if (! $package) {
            throw new InvalidArgumentException('Contract has no package.');
        }

        $dishes = $this->packageDishes($package);
        if ($dishes->isEmpty()) {
            throw new InvalidArgumentException('Package has no dishes to shuffle.');
        }

        $start = Carbon::create($year, $month, 1)->startOfDay();
        $end = $start->copy()->endOfMonth();

        return DB::transaction(function () use ($contract, $package, $dishes, $start, $end): array {
            $schedules = SubscriberSchedule::query()
                ->where('subscribe_contract_id', $contract->id)
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->whereNotIn('status', ['delivered', 'cancelled'])
                ->orderBy('date')
                ->get();

            if ($schedules->isEmpty()) {
                return ['updated' => 0];
            }

            $pool = $dishes->shuffle()->values();
            $updated = 0;

            foreach ($schedules as $index => $schedule) {
                $dish = $pool[$index % $pool->count()];
                $schedule->fill([
                    'package_id' => $package->id,
                    'package_title' => $package->title,
                    'dish_id' => $dish->id,
                    'dish_title' => $dish->title,
                ])->save();
                $updated++;
            }

            return ['updated' => $updated];
        });
    }

    /**
     * @param  array{
     *     dish_id?: int|null,
     *     persons?: int,
     *     status?: string,
     *     notes?: string|null,
     * }  $data
     */
    public function update(SubscriberSchedule $schedule, array $data): SubscriberSchedule
    {
        if ($schedule->status === 'delivered' && ($data['status'] ?? $schedule->status) !== 'delivered') {
            throw new InvalidArgumentException('Delivered days cannot change status backward. Edit notes only if needed.');
        }

        if ($schedule->status === 'delivered' && isset($data['dish_id'], $data['persons'])) {
            // Allow notes-only style edits; block dish/persons after delivery.
            if ((int) ($data['dish_id'] ?? $schedule->dish_id) !== (int) $schedule->dish_id
                || (int) ($data['persons'] ?? $schedule->persons) !== (int) $schedule->persons) {
                throw new InvalidArgumentException('Delivered days cannot change meal or persons.');
            }
        }

        return DB::transaction(function () use ($schedule, $data): SubscriberSchedule {
            $persons = (int) ($data['persons'] ?? $schedule->persons);
            $unit = (float) $schedule->unit_price;

            if (array_key_exists('dish_id', $data) && $data['dish_id']) {
                $dish = Dish::query()->findOrFail((int) $data['dish_id']);
                $schedule->dish_id = $dish->id;
                $schedule->dish_title = $dish->title;
            }

            $schedule->persons = $persons;
            $schedule->total = Money::of($unit * $persons);

            if (array_key_exists('notes', $data)) {
                $schedule->notes = $data['notes'];
            }

            if (! empty($data['status'])) {
                $schedule->status = $data['status'];
            }

            $schedule->save();

            return $schedule->load([
                'contract.user',
                'package',
                'dish',
                'order',
            ]);
        });
    }

    public function markDelivered(SubscriberSchedule $schedule): SubscriberSchedule
    {
        if ($schedule->status === 'cancelled' || $schedule->status === 'skipped') {
            throw new InvalidArgumentException('Cancelled or skipped days cannot be marked delivered.');
        }

        return DB::transaction(function () use ($schedule): SubscriberSchedule {
            $schedule->loadMissing(['contract.user', 'package', 'dish', 'order']);

            $schedule->status = 'delivered';
            $schedule->save();

            if (! $schedule->order) {
                $this->createOrderFromSchedule($schedule);
            } elseif ($schedule->order->status !== 'delivered') {
                $schedule->order->status = 'delivered';
                $schedule->order->save();
            }

            return $schedule->fresh([
                'contract.user',
                'package',
                'dish',
                'order',
            ]);
        });
    }

    public function delete(SubscriberSchedule $schedule): void
    {
        if ($schedule->status === 'delivered' || $schedule->order()->exists()) {
            throw new InvalidArgumentException('Delivered (or ordered) days cannot be deleted. Mark skipped instead.');
        }

        $schedule->delete();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(SubscriberSchedule $schedule): array
    {
        $schedule->loadMissing(['contract.user', 'package', 'dish', 'order']);
        $status = StatusRegistry::get('schedule', (string) $schedule->status);
        $isDelivered = $schedule->status === 'delivered';
        $payment = $isDelivered
            ? StatusRegistry::get('payment', (string) ($schedule->payment_status ?: 'unpaid'))
            : null;
        $date = $schedule->date;

        return [
            'id' => $schedule->id,
            'subscribe_contract_id' => $schedule->subscribe_contract_id,
            'company_name' => $schedule->contract?->user?->company_name
                ?: $schedule->contract?->user?->name,
            'package_id' => $schedule->package_id,
            'package_title' => $schedule->package_title ?: $schedule->package?->title,
            'dish_id' => $schedule->dish_id,
            'dish_title' => $schedule->dish_title ?: $schedule->dish?->title,
            'date' => $date?->toDateString(),
            'date_formatted' => $date?->format('d M Y'),
            'day' => $date?->format('D'),
            'day_full' => $date?->format('l'),
            'week_of_month' => $schedule->week_of_month,
            'persons' => (int) $schedule->persons,
            'unit_price' => (float) $schedule->unit_price,
            'unit_price_formatted' => Money::format((float) $schedule->unit_price),
            'total' => (float) $schedule->total,
            'total_formatted' => Money::format((float) $schedule->total),
            'status' => $status['code'],
            'status_label' => $status['label'],
            'status_badge' => $status['badge'],
            'payment_status' => $payment['code'] ?? null,
            'payment_status_label' => $payment['label'] ?? null,
            'payment_status_badge' => $payment['badge'] ?? null,
            'notes' => $schedule->notes,
            'has_order' => (bool) $schedule->order,
            'order_id' => $schedule->order?->id,
            'order_number' => $schedule->order?->order_number,
            'can_edit' => ! in_array($schedule->status, ['delivered'], true),
            'can_deliver' => ! in_array($schedule->status, ['delivered', 'cancelled', 'skipped'], true),
            'can_delete' => ! in_array($schedule->status, ['delivered'], true) && ! $schedule->order,
        ];
    }

    /**
     * @return list<array{value: int, label: string}>
     */
    public function contractOptions(): array
    {
        return SubscribeContract::query()
            ->with('user:id,name,company_name')
            ->where('status', 'active')
            ->latest('id')
            ->get()
            ->map(fn (SubscribeContract $contract) => [
                'value' => $contract->id,
                'label' => ($contract->user?->company_name ?: $contract->user?->name ?: 'Contract')
                    .' · #'.$contract->id,
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{value: int, label: string}>
     */
    public function dishOptionsForContract(SubscribeContract $contract): array
    {
        $package = $contract->package()->with(['items.dish'])->first();
        if (! $package) {
            return [];
        }

        return $this->packageDishes($package)
            ->map(fn (Dish $dish) => [
                'value' => $dish->id,
                'label' => $dish->title,
            ])
            ->values()
            ->all();
    }

    /**
     * @return Collection<int, Dish>
     */
    private function packageDishes(Package $package): Collection
    {
        return $package->items
            ->map(fn ($item) => $item->dish)
            ->filter()
            ->unique('id')
            ->values();
    }

    private function createDay(
        SubscribeContract $contract,
        Package $package,
        Dish $dish,
        Carbon $date,
        ?int $persons = null,
    ): SubscriberSchedule {
        $personsCount = max(1, $persons ?? (int) $contract->persons);
        $unit = (float) $contract->agreed_total;

        return SubscriberSchedule::query()->create([
            'subscribe_contract_id' => $contract->id,
            'package_id' => $package->id,
            'dish_id' => $dish->id,
            'date' => $date->toDateString(),
            'week_of_month' => (int) ceil($date->day / 7),
            'dish_title' => $dish->title,
            'package_title' => $package->title,
            'persons' => $personsCount,
            'unit_price' => Money::of($unit),
            'total' => Money::of($unit * $personsCount),
            'status' => 'scheduled',
            'notes' => null,
        ]);
    }

    private function createDayFromTemplate(
        SubscribeContract $contract,
        Package $package,
        SubscriberSchedule $template,
        Carbon $date,
    ): SubscriberSchedule {
        $persons = max(1, (int) $template->persons);
        $unit = (float) $contract->agreed_total;
        $dishId = $template->dish_id;
        $dishTitle = $template->dish_title;

        if ($dishId) {
            $dish = Dish::query()->find($dishId);
            if ($dish) {
                $dishTitle = $dish->title;
            }
        }

        return SubscriberSchedule::query()->create([
            'subscribe_contract_id' => $contract->id,
            'package_id' => $package->id,
            'dish_id' => $dishId,
            'date' => $date->toDateString(),
            'week_of_month' => (int) ceil($date->day / 7),
            'dish_title' => $dishTitle ?: 'Scheduled meal',
            'package_title' => $package->title,
            'persons' => $persons,
            'unit_price' => Money::of($unit),
            'total' => Money::of($unit * $persons),
            'status' => 'scheduled',
            'notes' => null,
        ]);
    }

    private function createOrderFromSchedule(SubscriberSchedule $schedule): Order
    {
        $contract = $schedule->contract;
        $user = $contract?->user;

        $order = Order::query()->create([
            'order_number' => 'CC-S-'.Str::upper(Str::random(8)),
            'user_id' => $user?->id,
            'type' => OrderType::Subscription,
            'channel' => OrderChannel::Admin,
            'status' => 'delivered',
            'payment_method' => 'cod',
            'customer_name' => $user?->company_name ?: $user?->name,
            'customer_phone' => $user?->phone,
            'customer_email' => $user?->email,
            'delivery_address' => $user?->address,
            'delivery_fee' => Money::of(0),
            'subtotal' => Money::of($schedule->total),
            'list_amount' => Money::of($schedule->total),
            'discount_amount' => Money::of(0),
            'discount_percent' => Money::of(0),
            'quoted_amount' => Money::of($schedule->total),
            'subscribe_contract_id' => $schedule->subscribe_contract_id,
            'subscriber_schedule_id' => $schedule->id,
            'notes' => $schedule->notes,
            'placed_at' => $schedule->date?->toDateTimeString() ?? now(),
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'dish_id' => $schedule->dish_id,
            'dish_variation_id' => null,
            'title' => $schedule->dish_title ?: 'Subscription meal',
            'variation_label' => $schedule->package_title,
            'quantity' => max(1, (int) $schedule->persons),
            'unit_price' => Money::of($schedule->unit_price),
            'line_total' => Money::of($schedule->total),
        ]);

        OrderStatusHistory::query()->create([
            'order_id' => $order->id,
            'from_status' => null,
            'to_status' => 'delivered',
            'changed_by' => null,
            'note' => 'Created from schedule delivery',
        ]);

        return $order;
    }
}
