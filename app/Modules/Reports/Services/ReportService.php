<?php

namespace App\Modules\Reports\Services;

use App\Models\User;
use App\Modules\Expenses\Models\Expense;
use App\Modules\Orders\Models\Order;
use App\Modules\Subscribers\Models\SubscribeContract;
use App\Support\Enums\OrderChannel;
use App\Support\Enums\OrderType;
use App\Support\Money;
use App\Support\StatusRegistry;
use Illuminate\Support\Carbon;

class ReportService
{
    /**
     * @return list<array{key: string, title: string, description: string, href: string, permission: string}>
     */
    public function availableReports(?User $user): array
    {
        $all = [
            [
                'key' => 'orders',
                'title' => 'Orders',
                'description' => 'Volume by type, channel, and status for a date range.',
                'href' => '/admin/reports/orders',
                'permission' => 'reports.orders',
            ],
            [
                'key' => 'contracts',
                'title' => 'Contracts',
                'description' => 'Active subscriber contracts and agreed rates.',
                'href' => '/admin/reports/contracts',
                'permission' => 'reports.contracts',
            ],
            [
                'key' => 'earnings',
                'title' => 'Earnings',
                'description' => 'Paid order revenue (amount paid) by day and type.',
                'href' => '/admin/reports/earnings',
                'permission' => 'reports.earnings',
            ],
            [
                'key' => 'profit_loss',
                'title' => 'Profit & loss',
                'description' => 'Paid revenue vs expenses for a date range.',
                'href' => '/admin/reports/profit-loss',
                'permission' => 'reports.profit_loss',
            ],
        ];

        return array_values(array_filter(
            $all,
            fn (array $report) => $user?->can($report['permission']) ?? false,
        ));
    }

    /**
     * @return array{date_from: string, date_to: string}
     */
    public function defaultDateRange(): array
    {
        return [
            'date_from' => Carbon::now()->startOfMonth()->toDateString(),
            'date_to' => Carbon::now()->toDateString(),
        ];
    }

    /**
     * @param  array{date_from?: string|null, date_to?: string|null}  $filters
     * @return array{date_from: string, date_to: string}
     */
    public function resolveDateRange(array $filters): array
    {
        $defaults = $this->defaultDateRange();
        $from = $filters['date_from'] ?? $defaults['date_from'];
        $to = $filters['date_to'] ?? $defaults['date_to'];

        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        return [
            'date_from' => $from,
            'date_to' => $to,
        ];
    }

    /**
     * @param  array{date_from: string, date_to: string}  $range
     * @return array<string, mixed>
     */
    public function ordersReport(array $range): array
    {
        $query = Order::query()
            ->where(function ($builder) use ($range): void {
                $builder
                    ->whereBetween('placed_at', [
                        $range['date_from'].' 00:00:00',
                        $range['date_to'].' 23:59:59',
                    ])
                    ->orWhere(function ($inner) use ($range): void {
                        $inner->whereNull('placed_at')
                            ->whereDate('created_at', '>=', $range['date_from'])
                            ->whereDate('created_at', '<=', $range['date_to']);
                    });
            });

        $orders = (clone $query)->get(['id', 'type', 'channel', 'status', 'quoted_amount', 'amount_paid']);

        $byType = [];
        $byChannel = [];
        $byStatus = [];
        $quoted = 0.0;
        $paid = 0.0;

        foreach ($orders as $order) {
            $type = $order->type instanceof OrderType
                ? $order->type->value
                : (string) $order->type;
            $channel = $order->channel instanceof OrderChannel
                ? $order->channel->value
                : (string) $order->channel;
            $status = (string) $order->status;

            $byType[$type] = ($byType[$type] ?? 0) + 1;
            $byChannel[$channel] = ($byChannel[$channel] ?? 0) + 1;
            $byStatus[$status] = ($byStatus[$status] ?? 0) + 1;
            $quoted += (float) $order->quoted_amount;
            if ($order->amount_paid !== null) {
                $paid += (float) $order->amount_paid;
            }
        }

        return [
            'summary' => [
                'orders_count' => $orders->count(),
                'quoted_total' => $quoted,
                'quoted_total_formatted' => Money::format($quoted),
                'paid_total' => $paid,
                'paid_total_formatted' => Money::format($paid),
            ],
            'by_type' => $this->labeledCountRows($byType, fn (string $code) => OrderType::tryFrom($code)?->label() ?? $code),
            'by_channel' => $this->labeledCountRows($byChannel, fn (string $code) => OrderChannel::tryFrom($code)?->label() ?? $code),
            'by_status' => $this->labeledCountRows($byStatus, fn (string $code) => StatusRegistry::get('order', $code)['label']),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function contractsReport(): array
    {
        $contracts = SubscribeContract::query()
            ->with(['user:id,name,company_name', 'package:id,title'])
            ->orderByDesc('id')
            ->get();

        $active = $contracts->where('status', 'active');
        $agreedSum = (float) $active->sum(fn (SubscribeContract $c) => (float) $c->agreed_total);

        $byStatus = [];
        foreach ($contracts as $contract) {
            $status = (string) $contract->status;
            $byStatus[$status] = ($byStatus[$status] ?? 0) + 1;
        }

        $rows = $active->take(50)->map(function (SubscribeContract $contract) {
            $status = StatusRegistry::get('generic', (string) $contract->status);

            return [
                'id' => $contract->id,
                'subscriber' => $contract->user?->company_name ?: $contract->user?->name,
                'package' => $contract->package?->title,
                'agreed_total' => (float) $contract->agreed_total,
                'agreed_total_formatted' => Money::format((float) $contract->agreed_total),
                'status' => $status['code'],
                'status_label' => $status['label'],
                'status_badge' => $status['badge'],
            ];
        })->values()->all();

        return [
            'summary' => [
                'contracts_count' => $contracts->count(),
                'active_count' => $active->count(),
                'active_agreed_total' => $agreedSum,
                'active_agreed_total_formatted' => Money::format($agreedSum),
            ],
            'by_status' => $this->labeledCountRows(
                $byStatus,
                fn (string $code) => StatusRegistry::get('generic', $code)['label'],
            ),
            'active_rows' => $rows,
        ];
    }

    /**
     * @param  array{date_from: string, date_to: string}  $range
     * @return array<string, mixed>
     */
    public function earningsReport(array $range): array
    {
        $orders = Order::query()
            ->whereNotNull('amount_paid')
            ->where(function ($builder) use ($range): void {
                $builder
                    ->whereBetween('placed_at', [
                        $range['date_from'].' 00:00:00',
                        $range['date_to'].' 23:59:59',
                    ])
                    ->orWhere(function ($inner) use ($range): void {
                        $inner->whereNull('placed_at')
                            ->whereDate('updated_at', '>=', $range['date_from'])
                            ->whereDate('updated_at', '<=', $range['date_to']);
                    });
            })
            ->get(['id', 'type', 'amount_paid', 'placed_at', 'updated_at', 'created_at']);

        $total = 0.0;
        $byType = [];
        $byDay = [];

        foreach ($orders as $order) {
            $paid = (float) $order->amount_paid;
            $total += $paid;
            $type = $order->type instanceof OrderType
                ? $order->type->value
                : (string) $order->type;
            $byType[$type] = ($byType[$type] ?? 0) + $paid;

            $day = $order->placed_at?->toDateString()
                ?? $order->updated_at?->toDateString()
                ?? $order->created_at?->toDateString()
                ?? $range['date_from'];
            $byDay[$day] = ($byDay[$day] ?? 0) + $paid;
        }

        ksort($byDay);

        return [
            'summary' => [
                'paid_orders' => $orders->count(),
                'paid_total' => $total,
                'paid_total_formatted' => Money::format($total),
            ],
            'by_type' => collect($byType)
                ->map(fn (float $amount, string $code) => [
                    'code' => $code,
                    'label' => OrderType::tryFrom($code)?->label() ?? $code,
                    'amount' => $amount,
                    'amount_formatted' => Money::format($amount),
                ])
                ->values()
                ->all(),
            'by_day' => collect($byDay)
                ->map(fn (float $amount, string $day) => [
                    'date' => $day,
                    'amount' => $amount,
                    'amount_formatted' => Money::format($amount),
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * @param  array{date_from: string, date_to: string}  $range
     * @return array<string, mixed>
     */
    public function profitLossReport(array $range): array
    {
        $earnings = $this->earningsReport($range);
        $revenue = (float) $earnings['summary']['paid_total'];

        $expensesTotal = (float) Expense::query()
            ->whereDate('date', '>=', $range['date_from'])
            ->whereDate('date', '<=', $range['date_to'])
            ->sum('total');

        $byCategory = Expense::query()
            ->with('expenseCategory:id,name')
            ->whereDate('date', '>=', $range['date_from'])
            ->whereDate('date', '<=', $range['date_to'])
            ->get(['expense_category_id', 'total'])
            ->groupBy('expense_category_id')
            ->map(function ($group) {
                $amount = (float) $group->sum(fn ($row) => (float) $row->total);

                return [
                    'category' => $group->first()?->expenseCategory?->name ?? 'Uncategorized',
                    'amount' => $amount,
                    'amount_formatted' => Money::format($amount),
                ];
            })
            ->sortByDesc('amount')
            ->values()
            ->all();

        $net = (float) Money::subtract($revenue, $expensesTotal);

        return [
            'summary' => [
                'revenue' => $revenue,
                'revenue_formatted' => Money::format($revenue),
                'expenses' => $expensesTotal,
                'expenses_formatted' => Money::format($expensesTotal),
                'net' => $net,
                'net_formatted' => Money::format($net),
            ],
            'expense_by_category' => $byCategory,
            'earnings_by_type' => $earnings['by_type'],
        ];
    }

    /**
     * @param  array<string, int>  $counts
     * @param  callable(string): string  $labeler
     * @return list<array{code: string, label: string, count: int}>
     */
    private function labeledCountRows(array $counts, callable $labeler): array
    {
        arsort($counts);

        return collect($counts)
            ->map(fn (int $count, string $code) => [
                'code' => $code,
                'label' => $labeler($code),
                'count' => $count,
            ])
            ->values()
            ->all();
    }
}
