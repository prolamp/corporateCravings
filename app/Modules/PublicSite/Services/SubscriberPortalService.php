<?php

namespace App\Modules\PublicSite\Services;

use App\Models\User;
use App\Modules\Subscribers\Models\SubscribeContract;
use App\Modules\Subscribers\Models\SubscriberBillSlip;
use App\Modules\Subscribers\Models\SubscriberSchedule;
use App\Modules\Subscribers\Services\BillSlipService;
use App\Support\StatusRegistry;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class SubscriberPortalService
{
    /** @var array<string, string> */
    private const DAY_LABELS = [
        'mon' => 'Mon',
        'tue' => 'Tue',
        'wed' => 'Wed',
        'thu' => 'Thu',
        'fri' => 'Fri',
        'sat' => 'Sat',
        'sun' => 'Sun',
    ];

    public function __construct(
        private readonly BillSlipService $billSlips,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function dashboard(User $user): array
    {
        $contract = $this->activeContract($user);
        $delivered = $this->deliveredSchedulesQuery($user)->get();

        $upcoming = SubscriberSchedule::query()
            ->whereHas('contract', fn ($q) => $q->where('user_id', $user->id))
            ->whereDate('date', '>=', now()->toDateString())
            ->whereNotIn('status', ['cancelled', 'skipped'])
            ->orderBy('date')
            ->limit(7)
            ->get()
            ->map(fn (SubscriberSchedule $row): array => $this->presentSchedule($row))
            ->values();

        return [
            'stats' => [
                'delivered_orders' => $delivered->count(),
                'total_bill' => (float) $delivered->sum('total'),
                'total_bill_formatted' => $this->formatRs((float) $delivered->sum('total')),
                'persons_served' => (int) $delivered->sum('persons'),
            ],
            'contract' => $contract ? $this->presentContractSummary($contract) : null,
            'upcoming' => $upcoming->all(),
            'links' => [
                'schedules' => $user->can('portal.subscriber.schedules'),
                'history' => $user->can('portal.subscriber.history'),
                'contract' => $user->can('portal.subscriber.contract'),
                'bills' => $user->can('portal.subscriber.bills'),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function schedules(User $user, ?string $month = null): array
    {
        $monthCarbon = $this->resolveMonth($user, $month);
        $contractIds = $this->contractIdsForUser($user);

        $entries = collect();
        if ($contractIds !== []) {
            $entries = SubscriberSchedule::query()
                ->whereIn('subscribe_contract_id', $contractIds)
                ->whereYear('date', $monthCarbon->year)
                ->whereMonth('date', $monthCarbon->month)
                ->orderBy('date')
                ->get()
                ->map(fn (SubscriberSchedule $row): array => $this->presentSchedule($row))
                ->values();
        }

        $active = $this->activeContract($user);
        $anyContract = $active ?? $this->latestContract($user);

        return [
            'month' => $monthCarbon->format('Y-m'),
            'month_label' => $monthCarbon->format('F Y'),
            'prev_month' => $monthCarbon->copy()->subMonth()->format('Y-m'),
            'next_month' => $monthCarbon->copy()->addMonth()->format('Y-m'),
            'contract' => $anyContract ? $this->presentContractSummary($anyContract) : null,
            'entries' => $entries->all(),
            'has_contracts' => $contractIds !== [],
        ];
    }

    /**
     * @return array{entries: array{data: list<array<string, mixed>>, meta: array<string, mixed>}}
     */
    public function history(User $user): array
    {
        $paginator = SubscriberSchedule::query()
            ->where('status', 'delivered')
            ->whereHas('contract', fn ($q) => $q->where('user_id', $user->id))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return [
            'entries' => [
                'data' => $paginator->getCollection()
                    ->map(fn (SubscriberSchedule $row): array => $this->presentSchedule($row))
                    ->values()
                    ->all(),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function contract(User $user): array
    {
        $contract = $this->activeContract($user) ?? $this->latestContract($user);

        return [
            'contract' => $contract ? $this->presentContractDetail($contract) : null,
        ];
    }

    /**
     * @param  array{status?: string|null}  $filters
     * @return array<string, mixed>
     */
    public function bills(User $user, array $filters = []): array
    {
        $paginator = $this->billSlips->paginateForSubscriber($user, $filters);

        return [
            'slips' => [
                'data' => $paginator->getCollection()
                    ->map(fn (SubscriberBillSlip $slip) => $this->billSlips->toArray($slip))
                    ->values()
                    ->all(),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ],
            ],
            'filters' => [
                'status' => $filters['status'] ?? null,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function billShow(User $user, SubscriberBillSlip $slip): array
    {
        $this->assertOwnsSlip($user, $slip);

        return [
            'slip' => $this->billSlips->toArray($slip, includeItems: true),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function billReceipt(User $user, SubscriberBillSlip $slip): array
    {
        $this->assertOwnsSlip($user, $slip);

        return [
            'slip' => $this->billSlips->downloadPayload($slip),
        ];
    }

    private function assertOwnsSlip(User $user, SubscriberBillSlip $slip): void
    {
        $slip->loadMissing('contract');
        abort_unless(
            (int) $slip->contract?->user_id === (int) $user->id
            && in_array($slip->status, ['issued', 'paid'], true),
            403,
        );
    }

    private function activeContract(User $user): ?SubscribeContract
    {
        return SubscribeContract::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->with(['package:id,title,total_amount_per_person'])
            ->latest('id')
            ->first();
    }

    private function latestContract(User $user): ?SubscribeContract
    {
        return SubscribeContract::query()
            ->where('user_id', $user->id)
            ->with(['package:id,title,total_amount_per_person'])
            ->latest('id')
            ->first();
    }

    /**
     * @return list<int>
     */
    private function contractIdsForUser(User $user): array
    {
        return SubscribeContract::query()
            ->where('user_id', $user->id)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<SubscriberSchedule>
     */
    private function deliveredSchedulesQuery(User $user)
    {
        return SubscriberSchedule::query()
            ->where('status', 'delivered')
            ->whereHas('contract', fn ($q) => $q->where('user_id', $user->id));
    }

    private function resolveMonth(User $user, ?string $month): CarbonInterface
    {
        if ($month && preg_match('/^\d{4}-\d{2}$/', $month) === 1) {
            return Carbon::createFromFormat('Y-m-d', $month.'-01')->startOfMonth();
        }

        $nearest = SubscriberSchedule::query()
            ->whereHas('contract', fn ($q) => $q->where('user_id', $user->id))
            ->orderByDesc('date')
            ->value('date');

        // Prefer a month that has data: latest past/current month with entries, else latest overall.
        $today = now()->toDateString();
        $pastOrToday = SubscriberSchedule::query()
            ->whereHas('contract', fn ($q) => $q->where('user_id', $user->id))
            ->whereDate('date', '<=', $today)
            ->orderByDesc('date')
            ->value('date');

        $pick = $pastOrToday ?: $nearest;

        if ($pick) {
            return Carbon::parse($pick)->startOfMonth();
        }

        return Carbon::now()->startOfMonth();
    }

    /**
     * @return array<string, mixed>
     */
    private function presentSchedule(SubscriberSchedule $row): array
    {
        $status = StatusRegistry::get('schedule', (string) $row->status);
        $isDelivered = $row->status === 'delivered';
        $payment = $isDelivered
            ? StatusRegistry::get('payment', (string) ($row->payment_status ?: 'unpaid'))
            : null;

        return [
            'id' => $row->id,
            'date' => $row->date?->toDateString(),
            'date_formatted' => $row->date?->timezone(config('app.timezone'))->format('D, d M Y'),
            'dish_title' => $row->dish_title,
            'package_title' => $row->package_title,
            'persons' => (int) $row->persons,
            'unit_price_formatted' => $this->formatRs((float) $row->unit_price),
            'total_formatted' => $this->formatRs((float) $row->total),
            'status' => $status['code'],
            'status_label' => $status['label'],
            'status_badge' => $status['badge'],
            'payment_status' => $payment['code'] ?? null,
            'payment_status_label' => $payment['label'] ?? null,
            'payment_status_badge' => $payment['badge'] ?? null,
            'notes' => $row->notes,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function presentContractSummary(SubscribeContract $contract): array
    {
        $status = StatusRegistry::get('generic', (string) $contract->status);

        return [
            'id' => $contract->id,
            'package_title' => $contract->package?->title,
            'meal_type' => $contract->meal_type,
            'meal_type_label' => ucfirst((string) $contract->meal_type),
            'persons' => (int) $contract->persons,
            'agreed_total_formatted' => $this->formatRs((float) $contract->agreed_total),
            'status' => $status['code'],
            'status_label' => $status['label'],
            'status_badge' => $status['badge'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function presentContractDetail(SubscribeContract $contract): array
    {
        $summary = $this->presentContractSummary($contract);
        $days = array_values($contract->days_of_week ?? []);
        $persons = (int) $contract->persons;
        $agreed = (float) $contract->agreed_total;
        $dayBill = $agreed * $persons;

        return [
            ...$summary,
            'days_of_week' => $days,
            'days_of_week_labels' => collect($days)
                ->map(fn (string $day): string => self::DAY_LABELS[$day] ?? ucfirst($day))
                ->values()
                ->all(),
            'delivery_time' => $this->formatTime($contract->delivery_time),
            'has_discount' => (float) $contract->discount > 0,
            'package_amount_formatted' => $this->formatRs((float) $contract->package_amount),
            'discount_formatted' => $this->formatRs((float) $contract->discount),
            'day_bill_formatted' => $this->formatRs($dayBill),
            'starts_on_formatted' => $contract->starts_on?->format('d M Y'),
            'ends_on_formatted' => $contract->ends_on?->format('d M Y'),
            'notes' => $contract->notes,
        ];
    }

    private function formatTime(mixed $time): ?string
    {
        if ($time === null || $time === '') {
            return null;
        }

        try {
            return Carbon::parse((string) $time)->format('h:i A');
        } catch (\Throwable) {
            return (string) $time;
        }
    }

    private function formatRs(float $amount): string
    {
        return 'Rs. '.number_format($amount, 0);
    }
}
