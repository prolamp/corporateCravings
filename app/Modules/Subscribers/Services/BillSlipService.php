<?php

namespace App\Modules\Subscribers\Services;

use App\Models\User;
use App\Modules\Subscribers\Models\SubscribeContract;
use App\Modules\Subscribers\Models\SubscriberBillSlip;
use App\Modules\Subscribers\Models\SubscriberBillSlipItem;
use App\Modules\Subscribers\Models\SubscriberSchedule;
use App\Support\Money;
use App\Support\StatusRegistry;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class BillSlipService
{
    /**
     * @param  array{
     *     search?: string|null,
     *     status?: string|null,
     *     user_id?: int|null,
     *     contract_id?: int|null,
     *     date_from?: string|null,
     *     date_to?: string|null,
     * }  $filters
     * @return LengthAwarePaginator<int, SubscriberBillSlip>
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = SubscriberBillSlip::query()
            ->with(['contract.user:id,name,company_name,email', 'creator:id,name'])
            ->withCount('items')
            ->latest('id');

        if (! empty($filters['contract_id'])) {
            $query->where('subscribe_contract_id', (int) $filters['contract_id']);
        }

        if (! empty($filters['user_id'])) {
            $query->whereHas('contract', fn ($q) => $q->where('user_id', (int) $filters['user_id']));
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($q) use ($search): void {
                $q->where('slip_number', 'like', "%{$search}%")
                    ->orWhereHas('contract.user', function ($user) use ($search): void {
                        $user->where('name', 'like', "%{$search}%")
                            ->orWhere('company_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('period_end', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('period_start', '<=', $filters['date_to']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * @param  array{
     *     status?: string|null,
     * }  $filters
     * @return LengthAwarePaginator<int, SubscriberBillSlip>
     */
    public function paginateForSubscriber(User $user, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = SubscriberBillSlip::query()
            ->with(['contract.package:id,title'])
            ->withCount('items')
            ->whereHas('contract', fn ($q) => $q->where('user_id', $user->id))
            ->whereIn('status', ['issued', 'paid'])
            ->latest('id');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Delivered schedules not already on an open/paid slip.
     *
     * @return Collection<int, SubscriberSchedule>
     */
    public function eligibleDeliveries(
        SubscribeContract $contract,
        ?string $from = null,
        ?string $to = null,
        ?SubscriberBillSlip $exceptSlip = null,
    ): Collection {
        $blockedIds = SubscriberBillSlipItem::query()
            ->whereHas('slip', function ($q) use ($exceptSlip): void {
                $q->whereIn('status', ['draft', 'issued', 'paid']);
                if ($exceptSlip) {
                    $q->whereKeyNot($exceptSlip->id);
                }
            })
            ->pluck('subscriber_schedule_id')
            ->all();

        $query = SubscriberSchedule::query()
            ->where('subscribe_contract_id', $contract->id)
            ->where('status', 'delivered')
            ->orderBy('date')
            ->orderBy('id');

        if ($blockedIds !== []) {
            $query->whereNotIn('id', $blockedIds);
        }

        if ($from) {
            $query->whereDate('date', '>=', $from);
        }

        if ($to) {
            $query->whereDate('date', '<=', $to);
        }

        return $query->get();
    }

    /**
     * @param  list<int>  $scheduleIds
     */
    public function create(
        User $actor,
        SubscribeContract $contract,
        array $scheduleIds,
        float|int|string $adjustment = 0,
        ?string $notes = null,
    ): SubscriberBillSlip {
        $schedules = $this->resolveEligibleSchedules($contract, $scheduleIds);

        return DB::transaction(function () use ($actor, $contract, $schedules, $adjustment, $notes): SubscriberBillSlip {
            $subtotal = (float) $schedules->sum(fn (SubscriberSchedule $s) => (float) $s->total);
            $adj = Money::of($adjustment);
            $total = Money::add($subtotal, $adj);
            $dates = $schedules->pluck('date')->filter()->sort();

            $slip = SubscriberBillSlip::query()->create([
                'subscribe_contract_id' => $contract->id,
                'slip_number' => $this->nextSlipNumber(),
                'period_start' => $dates->first()?->toDateString() ?? now()->toDateString(),
                'period_end' => $dates->last()?->toDateString() ?? now()->toDateString(),
                'subtotal' => Money::of($subtotal),
                'adjustment' => $adj,
                'total' => $total,
                'status' => 'issued',
                'notes' => $notes,
                'created_by' => $actor->id,
            ]);

            foreach ($schedules as $schedule) {
                $this->attachSchedule($slip, $schedule);
            }

            return $slip->load(['contract.user', 'items.schedule', 'creator']);
        });
    }

    /**
     * @param  array{
     *     schedule_ids?: list<int>|null,
     *     adjustment?: float|int|string|null,
     *     notes?: string|null,
     * }  $data
     */
    public function update(User $actor, SubscriberBillSlip $slip, array $data): SubscriberBillSlip
    {
        if (! $slip->isEditable()) {
            throw new InvalidArgumentException('Paid or cancelled slips cannot be edited.');
        }

        return DB::transaction(function () use ($slip, $data): SubscriberBillSlip {
            $slip->loadMissing('contract');
            $contract = $slip->contract;
            if (! $contract) {
                throw new InvalidArgumentException('Bill slip has no contract.');
            }

            if (array_key_exists('schedule_ids', $data) && is_array($data['schedule_ids'])) {
                $schedules = $this->resolveEligibleSchedules(
                    $contract,
                    $data['schedule_ids'],
                    $slip,
                );

                // Detach current items / schedule links
                $currentScheduleIds = $slip->items()->pluck('subscriber_schedule_id');
                SubscriberSchedule::query()
                    ->whereIn('id', $currentScheduleIds)
                    ->where('subscriber_bill_slip_id', $slip->id)
                    ->update([
                        'subscriber_bill_slip_id' => null,
                        'payment_status' => 'unpaid',
                    ]);
                $slip->items()->delete();

                foreach ($schedules as $schedule) {
                    $this->attachSchedule($slip, $schedule);
                }

                $subtotal = (float) $schedules->sum(fn (SubscriberSchedule $s) => (float) $s->total);
                $dates = $schedules->pluck('date')->filter()->sort();
                $slip->subtotal = Money::of($subtotal);
                $slip->period_start = $dates->first()?->toDateString() ?? $slip->period_start;
                $slip->period_end = $dates->last()?->toDateString() ?? $slip->period_end;
            }

            if (array_key_exists('adjustment', $data) && $data['adjustment'] !== null && $data['adjustment'] !== '') {
                $slip->adjustment = Money::of($data['adjustment']);
            }

            if (array_key_exists('notes', $data)) {
                $slip->notes = $data['notes'];
            }

            $slip->total = Money::add((float) $slip->subtotal, (float) $slip->adjustment);
            $slip->save();

            return $slip->fresh(['contract.user', 'items.schedule', 'creator']);
        });
    }

    public function markPaid(User $actor, SubscriberBillSlip $slip): SubscriberBillSlip
    {
        if ($slip->isCancelled()) {
            throw new InvalidArgumentException('Cancelled slips cannot be marked paid.');
        }

        if ($slip->isPaid()) {
            return $slip->load(['contract.user', 'items.schedule', 'creator', 'paidByUser']);
        }

        return DB::transaction(function () use ($actor, $slip): SubscriberBillSlip {
            $slip->load(['items.schedule.order']);

            $slip->forceFill([
                'status' => 'paid',
                'paid_at' => now(),
                'paid_by' => $actor->id,
            ])->save();

            foreach ($slip->items as $item) {
                $schedule = $item->schedule;
                if (! $schedule) {
                    continue;
                }

                $schedule->forceFill([
                    'payment_status' => 'paid',
                    'subscriber_bill_slip_id' => $slip->id,
                ])->save();

                $order = $schedule->order;
                if ($order) {
                    $order->forceFill([
                        'amount_paid' => Money::of($item->line_total),
                    ])->save();
                }
            }

            return $slip->fresh(['contract.user', 'items.schedule', 'creator', 'paidByUser']);
        });
    }

    public function markUnpaid(User $actor, SubscriberBillSlip $slip): SubscriberBillSlip
    {
        if (! $slip->isPaid()) {
            throw new InvalidArgumentException('Only paid slips can be marked unpaid.');
        }

        return DB::transaction(function () use ($slip): SubscriberBillSlip {
            $slip->load(['items.schedule.order']);

            $slip->forceFill([
                'status' => 'issued',
                'paid_at' => null,
                'paid_by' => null,
            ])->save();

            $this->revertSchedulePayments($slip, clearLink: false);

            return $slip->fresh(['contract.user', 'items.schedule', 'creator']);
        });
    }

    public function cancel(User $actor, SubscriberBillSlip $slip): SubscriberBillSlip
    {
        if ($slip->isCancelled()) {
            return $slip;
        }

        return DB::transaction(function () use ($slip): SubscriberBillSlip {
            $slip->load(['items.schedule.order']);

            $this->revertSchedulePayments($slip, clearLink: true);

            $slip->forceFill([
                'status' => 'cancelled',
                'paid_at' => null,
                'paid_by' => null,
            ])->save();

            return $slip->fresh(['contract.user', 'items.schedule', 'creator']);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function downloadPayload(SubscriberBillSlip $slip): array
    {
        $slip->loadMissing([
            'contract.user',
            'contract.package:id,title',
            'items' => fn ($q) => $q->orderBy('date')->orderBy('id'),
            'creator:id,name',
            'paidByUser:id,name',
        ]);

        return $this->toArray($slip, includeItems: true, receipt: true);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(SubscriberBillSlip $slip, bool $includeItems = false, bool $receipt = false): array
    {
        $slip->loadMissing(['contract.user', 'contract.package:id,title']);
        $status = StatusRegistry::get('bill_slip', (string) $slip->status);
        $user = $slip->contract?->user;

        $payload = [
            'id' => $slip->id,
            'slip_number' => $slip->slip_number,
            'subscribe_contract_id' => $slip->subscribe_contract_id,
            'subscriber_id' => $user?->id,
            'subscriber_name' => $user?->name,
            'company_name' => $user?->company_name ?: $user?->name,
            'package_title' => $slip->contract?->package?->title,
            'period_start' => $slip->period_start?->toDateString(),
            'period_end' => $slip->period_end?->toDateString(),
            'period_label' => $this->periodLabel($slip),
            'subtotal' => (float) $slip->subtotal,
            'subtotal_formatted' => Money::format((float) $slip->subtotal),
            'adjustment' => (float) $slip->adjustment,
            'adjustment_formatted' => Money::format((float) $slip->adjustment),
            'total' => (float) $slip->total,
            'total_formatted' => Money::format((float) $slip->total),
            'status' => $status['code'],
            'status_label' => $status['label'],
            'status_badge' => $status['badge'],
            'paid_at' => $slip->paid_at?->timezone(config('app.timezone'))->toDateTimeString(),
            'paid_at_formatted' => $slip->paid_at
                ? $slip->paid_at->timezone(config('app.timezone'))->format('d M Y H:i')
                : null,
            'notes' => $slip->notes,
            'items_count' => $slip->items_count ?? $slip->items()->count(),
            'created_at' => $slip->created_at?->timezone(config('app.timezone'))->toDateTimeString(),
            'created_at_formatted' => $slip->created_at
                ? $slip->created_at->timezone(config('app.timezone'))->format('d M Y')
                : null,
            'can_edit' => $slip->isEditable(),
            'can_mark_paid' => in_array($slip->status, ['draft', 'issued'], true),
            'can_mark_unpaid' => $slip->isPaid(),
            'can_cancel' => ! $slip->isCancelled(),
        ];

        if ($includeItems || $receipt) {
            $slip->loadMissing(['items' => fn ($q) => $q->orderBy('date')->orderBy('id')]);
            $payload['items'] = $slip->items->map(fn (SubscriberBillSlipItem $item) => [
                'id' => $item->id,
                'subscriber_schedule_id' => $item->subscriber_schedule_id,
                'date' => $item->date?->toDateString(),
                'date_formatted' => $item->date?->format('D, d M Y'),
                'dish_title' => $item->dish_title,
                'persons' => (int) $item->persons,
                'unit_price' => (float) $item->unit_price,
                'unit_price_formatted' => Money::format((float) $item->unit_price),
                'line_total' => (float) $item->line_total,
                'line_total_formatted' => Money::format((float) $item->line_total),
            ])->values()->all();
        }

        if ($receipt) {
            $payload['subscriber_phone'] = $user?->phone;
            $payload['subscriber_email'] = $user?->email;
            $payload['subscriber_address'] = $user?->address;
            $payload['created_by_name'] = $slip->creator?->name;
            $payload['paid_by_name'] = $slip->paidByUser?->name;
            $payload['site_name'] = config('app.name', 'Corporate Cravings');
        }

        return $payload;
    }

    /**
     * @return list<array{value: int, label: string}>
     */
    public function contractOptions(?int $userId = null): array
    {
        $query = SubscribeContract::query()
            ->with('user:id,name,company_name')
            ->latest('id');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query->get()
            ->map(fn (SubscribeContract $contract) => [
                'value' => $contract->id,
                'label' => ($contract->user?->company_name ?: $contract->user?->name ?: 'Contract')
                    .' · #'.$contract->id
                    .' ('.$contract->status.')',
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{value: int, label: string, total: float, total_formatted: string, date: string|null, dish_title: string|null, persons: int}>
     */
    public function eligibleDeliveryOptions(
        SubscribeContract $contract,
        ?string $from = null,
        ?string $to = null,
        ?SubscriberBillSlip $exceptSlip = null,
    ): array {
        return $this->eligibleDeliveries($contract, $from, $to, $exceptSlip)
            ->map(fn (SubscriberSchedule $s) => [
                'value' => $s->id,
                'label' => ($s->date?->format('d M Y') ?? '—')
                    .' · '.($s->dish_title ?: 'Meal')
                    .' · '.Money::format((float) $s->total),
                'date' => $s->date?->toDateString(),
                'date_formatted' => $s->date?->format('D, d M Y'),
                'dish_title' => $s->dish_title,
                'persons' => (int) $s->persons,
                'total' => (float) $s->total,
                'total_formatted' => Money::format((float) $s->total),
            ])
            ->values()
            ->all();
    }

    /**
     * @param  list<int>  $scheduleIds
     * @return Collection<int, SubscriberSchedule>
     */
    private function resolveEligibleSchedules(
        SubscribeContract $contract,
        array $scheduleIds,
        ?SubscriberBillSlip $exceptSlip = null,
    ): Collection {
        $ids = array_values(array_unique(array_map('intval', $scheduleIds)));
        if ($ids === []) {
            throw new InvalidArgumentException('Select at least one delivered schedule day.');
        }

        $eligible = $this->eligibleDeliveries($contract, exceptSlip: $exceptSlip)
            ->keyBy('id');

        $resolved = collect();
        foreach ($ids as $id) {
            $schedule = $eligible->get($id);
            if (! $schedule) {
                $existing = SubscriberSchedule::query()->find($id);
                if (! $existing || (int) $existing->subscribe_contract_id !== (int) $contract->id) {
                    throw new InvalidArgumentException('One or more selected days do not belong to this contract.');
                }
                if ($existing->status !== 'delivered') {
                    throw new InvalidArgumentException('Only delivered schedule days can be billed.');
                }
                throw new InvalidArgumentException('One or more selected days are already on another bill slip.');
            }
            $resolved->push($schedule);
        }

        return $resolved;
    }

    private function attachSchedule(SubscriberBillSlip $slip, SubscriberSchedule $schedule): void
    {
        SubscriberBillSlipItem::query()->create([
            'subscriber_bill_slip_id' => $slip->id,
            'subscriber_schedule_id' => $schedule->id,
            'date' => $schedule->date?->toDateString(),
            'dish_title' => $schedule->dish_title,
            'persons' => (int) $schedule->persons,
            'unit_price' => Money::of($schedule->unit_price),
            'line_total' => Money::of($schedule->total),
        ]);

        $schedule->forceFill([
            'subscriber_bill_slip_id' => $slip->id,
            'payment_status' => $slip->isPaid() ? 'paid' : 'unpaid',
        ])->save();
    }

    private function revertSchedulePayments(SubscriberBillSlip $slip, bool $clearLink): void
    {
        foreach ($slip->items as $item) {
            $schedule = $item->schedule;
            if (! $schedule) {
                continue;
            }

            if ((int) $schedule->subscriber_bill_slip_id === (int) $slip->id) {
                $schedule->forceFill([
                    'payment_status' => 'unpaid',
                    'subscriber_bill_slip_id' => $clearLink ? null : $slip->id,
                ])->save();
            }

            $order = $schedule->order;
            if ($order && $order->amount_paid !== null) {
                $order->forceFill(['amount_paid' => null])->save();
            }
        }
    }

    private function nextSlipNumber(): string
    {
        do {
            $number = 'CC-BS-'.Str::upper(Str::random(8));
        } while (SubscriberBillSlip::withTrashed()->where('slip_number', $number)->exists());

        return $number;
    }

    private function periodLabel(SubscriberBillSlip $slip): string
    {
        $start = $slip->period_start;
        $end = $slip->period_end;
        if (! $start || ! $end) {
            return '—';
        }

        if ($start->isSameDay($end)) {
            return $start->format('d M Y');
        }

        if ($start->isSameMonth($end)) {
            return $start->format('d').'–'.$end->format('d M Y');
        }

        return $start->format('d M Y').' – '.$end->format('d M Y');
    }
}
