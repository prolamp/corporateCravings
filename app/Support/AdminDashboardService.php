<?php

namespace App\Support;

use App\Models\User;
use App\Modules\Expenses\Models\Expense;
use App\Modules\Orders\Models\Order;
use App\Modules\Subscribers\Models\SubscribeContract;
use App\Modules\Subscribers\Models\SubscriberSchedule;
use App\Support\Enums\UserRole;
use Illuminate\Support\Carbon;

class AdminDashboardService
{
    /**
     * @param  list<string>  $widgets
     * @return array{
     *     stats: array<string, int|float>,
     *     recent_orders: list<array<string, mixed>>,
     *     todays_schedule: list<array<string, mixed>>,
     * }
     */
    public function build(array $widgets): array
    {
        return [
            'stats' => $this->stats($widgets),
            'recent_orders' => $this->recentOrders(),
            'todays_schedule' => $this->todaysSchedule(),
        ];
    }

    /**
     * @param  list<string>  $widgets
     * @return array<string, int|float>
     */
    public function stats(array $widgets): array
    {
        $today = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();
        $stats = [];

        if (in_array('orders', $widgets, true)) {
            $stats['orders'] = Order::query()->count();
            $stats['orders_today'] = Order::query()
                ->where(function ($query) use ($today): void {
                    $query->whereDate('placed_at', $today)
                        ->orWhere(function ($inner) use ($today): void {
                            $inner->whereNull('placed_at')->whereDate('created_at', $today);
                        });
                })
                ->count();
        }

        if (in_array('earnings', $widgets, true)) {
            $stats['earnings'] = (float) Order::query()
                ->whereNotNull('amount_paid')
                ->sum('amount_paid');
            $stats['earnings_today'] = (float) Order::query()
                ->whereNotNull('amount_paid')
                ->where(function ($query) use ($today): void {
                    $query->whereDate('placed_at', $today)
                        ->orWhere(function ($inner) use ($today): void {
                            $inner->whereNull('placed_at')->whereDate('updated_at', $today);
                        });
                })
                ->sum('amount_paid');
            $stats['earnings_mtd'] = (float) Order::query()
                ->whereNotNull('amount_paid')
                ->where(function ($query) use ($monthStart): void {
                    $query->where('placed_at', '>=', $monthStart)
                        ->orWhere(function ($inner) use ($monthStart): void {
                            $inner->whereNull('placed_at')->where('created_at', '>=', $monthStart);
                        });
                })
                ->sum('amount_paid');
        }

        if (in_array('expenses', $widgets, true)) {
            $stats['expenses'] = (float) Expense::query()->sum('total');
            $stats['expenses_mtd'] = (float) Expense::query()
                ->whereDate('date', '>=', $monthStart)
                ->sum('total');
        }

        if (
            in_array('earnings', $widgets, true)
            && in_array('expenses', $widgets, true)
        ) {
            $stats['profit_mtd'] = (float) Money::subtract(
                $stats['earnings_mtd'] ?? 0,
                $stats['expenses_mtd'] ?? 0,
            );
        }

        if (in_array('corporate_contracts', $widgets, true)) {
            $stats['corporate_contracts'] = SubscribeContract::query()
                ->where('status', 'active')
                ->count();
        }

        if (in_array('pending_deliveries', $widgets, true)) {
            $stats['pending_deliveries'] = Order::query()
                ->whereIn('status', ['pending', 'confirmed', 'leave_for_delivery'])
                ->count();
            $stats['subscriber_deliveries_today'] = SubscriberSchedule::query()
                ->whereDate('date', $today)
                ->whereIn('status', ['scheduled', 'confirmed'])
                ->count();
        }

        if (in_array('customers', $widgets, true)) {
            $stats['customers'] = User::role(UserRole::Customer->value)->count();
        }

        return $stats;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function recentOrders(int $limit = 8): array
    {
        return Order::query()
            ->latest('placed_at')
            ->latest('id')
            ->limit($limit)
            ->get()
            ->map(function (Order $order): array {
                $status = StatusRegistry::get('order', (string) $order->status);

                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->customer_name,
                    'customer_phone' => $order->customer_phone,
                    'type' => $order->type?->value ?? (string) $order->type,
                    'channel' => $order->channel?->value ?? (string) $order->channel,
                    'status' => $status['code'],
                    'status_label' => $status['label'],
                    'status_badge' => $status['badge'],
                    'quoted_amount' => (float) $order->quoted_amount,
                    'placed_at' => optional($order->placed_at ?? $order->created_at)?->toIso8601String(),
                ];
            })
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function todaysSchedule(int $limit = 10): array
    {
        return SubscriberSchedule::query()
            ->with(['contract.user:id,name,company_name'])
            ->whereDate('date', Carbon::today())
            ->orderBy('id')
            ->limit($limit)
            ->get()
            ->map(function (SubscriberSchedule $schedule): array {
                $status = StatusRegistry::get('schedule', (string) $schedule->status);

                return [
                    'id' => $schedule->id,
                    'company' => $schedule->contract?->user?->company_name
                        ?: $schedule->contract?->user?->name
                        ?: '—',
                    'dish_title' => $schedule->dish_title,
                    'package_title' => $schedule->package_title,
                    'persons' => (int) $schedule->persons,
                    'status' => $status['code'],
                    'status_label' => $status['label'],
                    'status_badge' => $status['badge'],
                ];
            })
            ->all();
    }
}
