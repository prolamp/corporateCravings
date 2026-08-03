<?php

namespace App\Modules\PublicSite\Services;

use App\Modules\Orders\Models\Order;
use App\Modules\Orders\Models\OrderItem;
use App\Modules\Orders\Models\OrderItemAddon;
use App\Modules\Orders\Models\OrderStatusHistory;
use App\Support\ErrorCatalog;
use App\Support\StatusRegistry;

class TrackOrderService
{
    /** @var list<string> */
    private const PROGRESS_STEPS = [
        'pending',
        'confirmed',
        'leave_for_delivery',
        'delivered',
        'completed',
    ];

    /**
     * @return array{
     *     query: string,
     *     error: string|null,
     *     order: array<string, mixed>|null
     * }
     */
    public function payload(?string $orderNumber): array
    {
        $query = strtoupper(trim((string) $orderNumber));

        if ($query === '') {
            return [
                'query' => '',
                'error' => null,
                'order' => null,
            ];
        }

        $order = Order::query()
            ->whereRaw('UPPER(order_number) = ?', [$query])
            ->with(['deliveryZone:id,name', 'items.addons', 'statusHistories'])
            ->first();

        if (! $order instanceof Order) {
            return [
                'query' => $query,
                'error' => ErrorCatalog::message('order_not_found'),
                'order' => null,
            ];
        }

        return [
            'query' => $order->order_number,
            'error' => null,
            'order' => $this->present($order),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function present(Order $order): array
    {
        $status = StatusRegistry::get('order', (string) $order->status);

        return [
            'order_number' => $order->order_number,
            'status' => $status['code'],
            'status_label' => $status['label'],
            'status_badge' => $status['badge'],
            'payment_label' => $order->payment_method === 'cod'
                ? 'Cash on delivery'
                : ucfirst((string) $order->payment_method),
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'delivery_address' => $order->delivery_address,
            'delivery_zone' => $order->deliveryZone?->name,
            'total_formatted' => $this->formatRs((float) $order->quoted_amount),
            'placed_at_formatted' => $order->placed_at
                ? $order->placed_at->timezone(config('app.timezone'))->format('d M Y · h:i A')
                : null,
            'items' => $order->items->map(fn (OrderItem $item): array => $this->presentItem($item))->values()->all(),
            'progress' => $this->progressSteps((string) $order->status),
            'timeline' => $order->statusHistories
                ->sortBy('id')
                ->values()
                ->map(fn (OrderStatusHistory $row): array => $this->presentTimelineRow($row))
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function presentItem(OrderItem $item): array
    {
        $addons = $item->addons->map(fn (OrderItemAddon $addon): array => [
            'id' => $addon->id,
            'title' => $addon->title,
            'quantity' => (int) $addon->quantity,
            'line_total_formatted' => $this->formatRs((float) $addon->line_total),
        ])->values()->all();

        $addonsTotal = (float) $item->addons->sum(fn (OrderItemAddon $addon) => (float) $addon->line_total);

        return [
            'id' => $item->id,
            'title' => $item->title,
            'variation_label' => $item->variation_label,
            'quantity' => (int) $item->quantity,
            'unit_price_formatted' => $this->formatRs((float) $item->unit_price),
            'line_total_formatted' => $this->formatRs((float) $item->line_total + $addonsTotal),
            'addons' => $addons,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function presentTimelineRow(OrderStatusHistory $row): array
    {
        $to = StatusRegistry::get('order', (string) $row->to_status);

        return [
            'id' => $row->id,
            'status' => $to['code'],
            'status_label' => $to['label'],
            'status_badge' => $to['badge'],
            'at_formatted' => $row->created_at
                ? $row->created_at->timezone(config('app.timezone'))->format('d M Y · h:i A')
                : null,
        ];
    }

    /**
     * @return list<array{code: string, label: string, state: 'done'|'current'|'upcoming'|'cancelled'}>
     */
    private function progressSteps(string $current): array
    {
        if ($current === 'cancelled') {
            return [
                [
                    'code' => 'cancelled',
                    'label' => StatusRegistry::label('order', 'cancelled'),
                    'state' => 'cancelled',
                ],
            ];
        }

        $currentIndex = array_search($current, self::PROGRESS_STEPS, true);
        if ($currentIndex === false) {
            $currentIndex = 0;
        }

        return array_map(function (string $code, int $index) use ($currentIndex): array {
            $state = 'upcoming';
            if ($index < $currentIndex) {
                $state = 'done';
            } elseif ($index === $currentIndex) {
                $state = 'current';
            }

            return [
                'code' => $code,
                'label' => StatusRegistry::label('order', $code),
                'state' => $state,
            ];
        }, self::PROGRESS_STEPS, array_keys(self::PROGRESS_STEPS));
    }

    private function formatRs(float $amount): string
    {
        return 'Rs. '.number_format($amount, 0);
    }
}
