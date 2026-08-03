<?php

namespace App\Modules\Subscribers\Services;

use App\Models\User;
use App\Modules\Orders\Models\Order;
use App\Modules\Subscribers\Models\SubscribeContract;
use App\Modules\Users\Services\UserService;
use App\Support\Enums\UserRole;
use App\Support\Money;
use App\Support\RoleAccess;
use App\Support\StatusRegistry;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class SubscriberService
{
    public function __construct(
        private readonly UserService $users,
    ) {}

    /**
     * @param  array{search?: string|null, status?: string|null}  $filters
     * @return LengthAwarePaginator<int, User>
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = User::query()
            ->role(UserRole::Subscriber->value)
            ->with([
                'roles',
                'subscribeContracts' => fn ($q) => $q->with('package')->latest('id')->limit(1),
            ])
            ->latest('id');

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(User $actor, array $data): User
    {
        if (! RoleAccess::canCreateRole($actor, UserRole::Subscriber)) {
            throw new InvalidArgumentException('You are not allowed to create subscribers.');
        }

        $data['role'] = UserRole::Subscriber->value;

        return $this->users->create($actor, $data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $actor, User $subscriber, array $data): User
    {
        $this->assertSubscriber($subscriber);

        $data['role'] = UserRole::Subscriber->value;

        return $this->users->update($actor, $subscriber, $data);
    }

    public function delete(User $actor, User $subscriber): void
    {
        $this->assertSubscriber($subscriber);

        if ($subscriber->subscribeContracts()->exists()) {
            throw new InvalidArgumentException(
                'This subscriber has contracts. End or remove contracts before deleting.',
            );
        }

        $this->users->delete($actor, $subscriber);
    }

    public function toggleActive(User $actor, User $subscriber): User
    {
        $this->assertSubscriber($subscriber);

        return $this->users->toggleActive($actor, $subscriber);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(User $subscriber, ?User $actor = null): array
    {
        $this->assertSubscriber($subscriber);

        $base = $this->users->toArray($subscriber->loadMissing('roles'), $actor);

        $contract = $subscriber->relationLoaded('subscribeContracts')
            ? $subscriber->subscribeContracts->first()
            : $subscriber->subscribeContracts()->with('package')->latest('id')->first();

        $base['contract'] = $contract ? $this->contractSummary($contract) : null;

        return $base;
    }

    /**
     * Delivered subscription day bills for this company (orders marked delivered).
     *
     * @return list<array<string, mixed>>
     */
    public function deliveredDailyBills(User $subscriber, int $limit = 90): array
    {
        $this->assertSubscriber($subscriber);

        $orders = Order::query()
            ->where('status', 'delivered')
            ->whereHas('subscribeContract', fn ($q) => $q->where('user_id', $subscriber->id))
            ->with([
                'subscribeContract:id,user_id,meal_type',
                'subscriberSchedule:id,date,persons,dish_title,package_title,total,unit_price',
                'items:id,order_id,title,quantity',
            ])
            ->latest('id')
            ->limit($limit)
            ->get();

        return $orders
            ->map(function (Order $order): array {
                $schedule = $order->subscriberSchedule;
                $date = $schedule?->date ?? $order->placed_at?->toDateString();
                $dateCarbon = $date ? Carbon::parse($date) : null;

                $persons = $schedule
                    ? (int) $schedule->persons
                    : (int) $order->items->sum('quantity');

                $meal = $schedule?->dish_title
                    ?: $order->items->first()?->title
                    ?: $schedule?->package_title
                    ?: '—';

                $total = $schedule
                    ? (float) $schedule->total
                    : (float) $order->quoted_amount;

                $mealType = $order->subscribeContract?->meal_type;

                return [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'date' => $dateCarbon?->toDateString(),
                    'date_formatted' => $dateCarbon?->format('d M Y') ?? '—',
                    'day' => $dateCarbon?->format('D') ?? '—',
                    'day_full' => $dateCarbon?->format('l') ?? '—',
                    'persons' => $persons,
                    'meal' => $meal,
                    'meal_type' => $mealType,
                    'meal_type_label' => $mealType ? ucfirst($mealType) : null,
                    'total' => $total,
                    'total_formatted' => Money::format($total),
                    'delivered_at' => $order->updated_at?->toIso8601String(),
                ];
            })
            ->sortByDesc('date')
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function contractSummary(SubscribeContract $contract): array
    {
        $status = StatusRegistry::get('generic', $contract->status);

        return [
            'id' => $contract->id,
            'package_id' => $contract->package_id,
            'package_title' => $contract->package?->title,
            'meal_type' => $contract->meal_type,
            'persons' => (int) $contract->persons,
            'delivery_time' => $contract->delivery_time,
            'package_amount' => (float) $contract->package_amount,
            'package_amount_formatted' => Money::format((float) $contract->package_amount),
            'discount' => (float) $contract->discount,
            'discount_formatted' => Money::format((float) $contract->discount),
            'agreed_total' => (float) $contract->agreed_total,
            'agreed_total_formatted' => Money::format((float) $contract->agreed_total),
            'status' => $status['code'],
            'status_label' => $status['label'],
            'status_badge' => $status['badge'],
            'starts_on' => $contract->starts_on?->toDateString(),
            'ends_on' => $contract->ends_on?->toDateString(),
            'notes' => $contract->notes,
        ];
    }

    public function assertSubscriber(User $user): void
    {
        if (! $user->hasRole(UserRole::Subscriber->value)) {
            abort(404);
        }
    }
}
