<?php

namespace App\Modules\Subscribers\Services;

use App\Models\User;
use App\Modules\Packages\Models\Package;
use App\Modules\Subscribers\Models\SubscribeContract;
use App\Modules\Subscribers\Models\SubscriberPackageHistory;
use App\Support\Enums\MealType;
use App\Support\Enums\UserRole;
use App\Support\Money;
use App\Support\StatusRegistry;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SubscribeContractService
{
    /**
     * @param  array{
     *     search?: string|null,
     *     status?: string|null,
     *     user_id?: int|null,
     * }  $filters
     * @return LengthAwarePaginator<int, SubscribeContract>
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = SubscribeContract::query()
            ->with(['user', 'package'])
            ->withCount(['schedules', 'orders'])
            ->latest('id');

        if (! empty($filters['user_id'])) {
            $query->where('user_id', (int) $filters['user_id']);
        }

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($q) use ($search): void {
                $q->whereHas('user', function ($user) use ($search): void {
                    $user->where('name', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                })->orWhereHas('package', function ($package) use ($search): void {
                    $package->where('title', 'like', "%{$search}%");
                });
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * @param  array{
     *     user_id: int,
     *     package_id: int,
     *     meal_type: string,
     *     days_of_week: list<string>,
     *     persons: int,
     *     delivery_time?: string|null,
     *     package_amount: float|int|string,
     *     discount?: float|int|string|null,
     *     status: string,
     *     starts_on?: string|null,
     *     ends_on?: string|null,
     *     notes?: string|null,
     * }  $data
     */
    public function create(array $data): SubscribeContract
    {
        $subscriber = $this->resolveSubscriber((int) $data['user_id']);
        $package = Package::query()->findOrFail((int) $data['package_id']);

        return DB::transaction(function () use ($data, $subscriber, $package): SubscribeContract {
            $pricing = $this->resolvePricing($package, $data['discount'] ?? 0);

            $contract = SubscribeContract::query()->create([
                'user_id' => $subscriber->id,
                'package_id' => $package->id,
                'meal_type' => $data['meal_type'],
                'days_of_week' => array_values($data['days_of_week']),
                'persons' => (int) $data['persons'],
                'delivery_time' => $this->normalizeTime($data['delivery_time'] ?? null),
                'package_amount' => $pricing['package_amount'],
                'discount' => $pricing['discount'],
                'agreed_total' => $pricing['agreed_total'],
                'status' => $data['status'],
                'starts_on' => $data['starts_on'] ?? null,
                'ends_on' => $data['ends_on'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->recordHistory($contract, $package, effectiveFrom: now());

            return $contract->load(['user', 'package'])->loadCount(['schedules', 'orders']);
        });
    }

    /**
     * @param  array{
     *     user_id: int,
     *     package_id: int,
     *     meal_type: string,
     *     days_of_week: list<string>,
     *     persons: int,
     *     delivery_time?: string|null,
     *     package_amount: float|int|string,
     *     discount?: float|int|string|null,
     *     status: string,
     *     starts_on?: string|null,
     *     ends_on?: string|null,
     *     notes?: string|null,
     * }  $data
     */
    public function update(SubscribeContract $contract, array $data): SubscribeContract
    {
        $subscriber = $this->resolveSubscriber((int) $data['user_id']);
        $package = Package::query()->findOrFail((int) $data['package_id']);

        return DB::transaction(function () use ($contract, $data, $subscriber, $package): SubscribeContract {
            $pricing = $this->resolvePricing($package, $data['discount'] ?? 0);
            $pricingChanged = $this->pricingOrPackageChanged($contract, $package, $pricing, (int) $data['persons']);

            $contract->fill([
                'user_id' => $subscriber->id,
                'package_id' => $package->id,
                'meal_type' => $data['meal_type'],
                'days_of_week' => array_values($data['days_of_week']),
                'persons' => (int) $data['persons'],
                'delivery_time' => $this->normalizeTime($data['delivery_time'] ?? null),
                'package_amount' => $pricing['package_amount'],
                'discount' => $pricing['discount'],
                'agreed_total' => $pricing['agreed_total'],
                'status' => $data['status'],
                'starts_on' => $data['starts_on'] ?? null,
                'ends_on' => $data['ends_on'] ?? null,
                'notes' => $data['notes'] ?? null,
            ])->save();

            if ($pricingChanged) {
                $this->closeOpenHistory($contract);
                $this->recordHistory($contract->fresh(), $package, effectiveFrom: now());
            }

            return $contract->load(['user', 'package', 'packageHistory'])
                ->loadCount(['schedules', 'orders']);
        });
    }

    public function delete(SubscribeContract $contract): void
    {
        if ($contract->schedules()->exists()) {
            throw new InvalidArgumentException(
                'This contract has schedules. Expire it instead of deleting.',
            );
        }

        if ($contract->orders()->exists()) {
            throw new InvalidArgumentException(
                'This contract has orders. Expire it instead of deleting.',
            );
        }

        $contract->delete();
    }

    public function toggleActive(SubscribeContract $contract): SubscribeContract
    {
        if ($contract->status === 'active') {
            $contract->status = 'inactive';
            $contract->ends_on = $contract->ends_on ?? now()->toDateString();
        } elseif (in_array($contract->status, ['inactive', 'expired', 'draft'], true)) {
            $contract->status = 'active';
            if ($contract->ends_on && $contract->ends_on->isPast()) {
                $contract->ends_on = null;
            }
        } else {
            $contract->status = 'active';
        }

        $contract->save();

        return $contract->load(['user', 'package'])->loadCount(['schedules', 'orders']);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(SubscribeContract $contract): array
    {
        $contract->loadMissing(['user', 'package']);
        $status = StatusRegistry::get('generic', $contract->status);

        $history = $contract->relationLoaded('packageHistory')
            ? $contract->packageHistory
            : collect();

        $persons = (int) $contract->persons;
        $agreed = (float) $contract->agreed_total;
        $mealDaysPerWeek = count($contract->days_of_week ?? []);
        $dayBill = $agreed * $persons;
        // Rough month: meal days/week × ~4.3 weeks (actual bills come from schedules/orders later).
        $monthBillEstimate = $dayBill * $mealDaysPerWeek * 4.3;

        return [
            'id' => $contract->id,
            'user_id' => $contract->user_id,
            'subscriber_name' => $contract->user?->name,
            'company_name' => $contract->user?->company_name,
            'package_id' => $contract->package_id,
            'package_title' => $contract->package?->title,
            'package_public_total' => $contract->package
                ? (float) $contract->package->total_amount_per_person
                : null,
            'package_public_total_formatted' => $contract->package
                ? Money::format((float) $contract->package->total_amount_per_person)
                : null,
            'meal_type' => $contract->meal_type,
            'days_of_week' => array_values($contract->days_of_week ?? []),
            'meal_days_per_week' => $mealDaysPerWeek,
            'persons' => $persons,
            'delivery_time' => $this->formatTimeForForm($contract->delivery_time),
            'package_amount' => (float) $contract->package_amount,
            'package_amount_formatted' => Money::format((float) $contract->package_amount),
            'discount' => (float) $contract->discount,
            'discount_formatted' => Money::format((float) $contract->discount),
            'agreed_total' => $agreed,
            'agreed_total_formatted' => Money::format($agreed),
            'day_bill' => $dayBill,
            'day_bill_formatted' => Money::format($dayBill),
            'month_bill_estimate' => $monthBillEstimate,
            'month_bill_estimate_formatted' => Money::format($monthBillEstimate),
            'status' => $status['code'],
            'status_label' => $status['label'],
            'status_badge' => $status['badge'],
            'starts_on' => $contract->starts_on?->toDateString(),
            'ends_on' => $contract->ends_on?->toDateString(),
            'notes' => $contract->notes,
            'schedules_count' => (int) ($contract->schedules_count ?? $contract->schedules()->count()),
            'orders_count' => (int) ($contract->orders_count ?? $contract->orders()->count()),
            'can_delete' => (int) ($contract->schedules_count ?? $contract->schedules()->count()) === 0
                && (int) ($contract->orders_count ?? $contract->orders()->count()) === 0,
            'history' => $history->map(fn (SubscriberPackageHistory $row) => [
                'id' => $row->id,
                'package_title' => $row->package_title,
                'package_amount_formatted' => Money::format((float) $row->package_amount),
                'discount_formatted' => Money::format((float) $row->discount),
                'agreed_total_formatted' => Money::format((float) $row->agreed_total),
                'persons' => (int) $row->persons,
                'effective_from' => $row->effective_from?->toIso8601String(),
                'effective_to' => $row->effective_to?->toIso8601String(),
            ])->values()->all(),
            'created_at' => $contract->created_at?->toIso8601String(),
            'updated_at' => $contract->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @return list<array{value: int, label: string, public_total: float, public_total_formatted: string}>
     */
    public function packageOptions(?int $includePackageId = null): array
    {
        $query = Package::query()
            ->where(function ($q) use ($includePackageId): void {
                $q->where('status', 'active');
                if ($includePackageId) {
                    $q->orWhere('id', $includePackageId);
                }
            })
            ->orderBy('sort_order')
            ->orderBy('title');

        return $query
            ->get(['id', 'title', 'total_amount_per_person'])
            ->map(fn (Package $package) => [
                'value' => $package->id,
                'label' => $package->title,
                'public_total' => (float) $package->total_amount_per_person,
                'public_total_formatted' => Money::format((float) $package->total_amount_per_person),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{value: int, label: string}>
     */
    public function subscriberOptions(): array
    {
        return User::query()
            ->role(UserRole::Subscriber->value)
            ->orderBy('company_name')
            ->orderBy('name')
            ->get(['id', 'name', 'company_name'])
            ->map(fn (User $user) => [
                'value' => $user->id,
                'label' => $user->company_name
                    ? "{$user->company_name} ({$user->name})"
                    : $user->name,
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function mealTypeOptions(): array
    {
        return array_map(
            fn (string $value) => [
                'value' => $value,
                'label' => ucfirst($value),
            ],
            MealType::values(),
        );
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function dayOptions(): array
    {
        return [
            ['value' => 'mon', 'label' => 'Mon'],
            ['value' => 'tue', 'label' => 'Tue'],
            ['value' => 'wed', 'label' => 'Wed'],
            ['value' => 'thu', 'label' => 'Thu'],
            ['value' => 'fri', 'label' => 'Fri'],
            ['value' => 'sat', 'label' => 'Sat'],
            ['value' => 'sun', 'label' => 'Sun'],
        ];
    }

    private function resolveSubscriber(int $userId): User
    {
        $user = User::query()->findOrFail($userId);

        if (! $user->hasRole(UserRole::Subscriber->value)) {
            throw new InvalidArgumentException('Selected user must be a subscriber.');
        }

        return $user;
    }

    /**
     * Package amount always mirrors the package catalog total; only discount is negotiated.
     *
     * @return array{package_amount: string, discount: string, agreed_total: string}
     */
    private function resolvePricing(Package $package, float|int|string|null $discount): array
    {
        $packageAmount = Money::of($package->total_amount_per_person);
        $discountAmount = Money::of($discount ?? 0);

        if ((float) $discountAmount > (float) $packageAmount) {
            throw new InvalidArgumentException('Discount cannot exceed package rate.');
        }

        return [
            'package_amount' => $packageAmount,
            'discount' => $discountAmount,
            'agreed_total' => Money::subtract($packageAmount, $discountAmount),
        ];
    }

    /**
     * @param  array{package_amount: string, discount: string, agreed_total: string}  $pricing
     */
    private function pricingOrPackageChanged(
        SubscribeContract $contract,
        Package $package,
        array $pricing,
        int $persons,
    ): bool {
        return (int) $contract->package_id !== (int) $package->id
            || (float) $contract->package_amount !== (float) $pricing['package_amount']
            || (float) $contract->discount !== (float) $pricing['discount']
            || (float) $contract->agreed_total !== (float) $pricing['agreed_total']
            || (int) $contract->persons !== $persons;
    }

    private function recordHistory(
        SubscribeContract $contract,
        Package $package,
        mixed $effectiveFrom,
    ): void {
        SubscriberPackageHistory::query()->create([
            'subscribe_contract_id' => $contract->id,
            'package_id' => $package->id,
            'package_title' => $package->title,
            'package_amount' => $contract->package_amount,
            'discount' => $contract->discount,
            'agreed_total' => $contract->agreed_total,
            'persons' => $contract->persons,
            'effective_from' => $effectiveFrom,
            'effective_to' => null,
        ]);
    }

    private function closeOpenHistory(SubscribeContract $contract): void
    {
        SubscriberPackageHistory::query()
            ->where('subscribe_contract_id', $contract->id)
            ->whereNull('effective_to')
            ->update(['effective_to' => now()]);
    }

    private function normalizeTime(?string $time): ?string
    {
        if ($time === null || trim($time) === '') {
            return null;
        }

        $time = trim($time);

        if (preg_match('/^\d{2}:\d{2}$/', $time) === 1) {
            return "{$time}:00";
        }

        return $time;
    }

    private function formatTimeForForm(mixed $time): ?string
    {
        if ($time === null || $time === '') {
            return null;
        }

        $raw = (string) $time;

        return strlen($raw) >= 5 ? substr($raw, 0, 5) : $raw;
    }
}
