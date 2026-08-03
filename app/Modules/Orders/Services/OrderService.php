<?php

namespace App\Modules\Orders\Services;

use App\Models\User;
use App\Modules\Addons\Models\Addon;
use App\Modules\Catalog\Models\DishVariation;
use App\Modules\Delivery\Models\DeliveryZone;
use App\Modules\Orders\Models\Order;
use App\Modules\Orders\Models\OrderItem;
use App\Modules\Orders\Models\OrderItemAddon;
use App\Modules\Orders\Models\OrderStatusHistory;
use App\Support\Enums\OrderChannel;
use App\Support\Enums\OrderType;
use App\Support\Money;
use App\Support\StatusRegistry;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class OrderService
{
    /**
     * @param  array{
     *     search?: string|null,
     *     type?: string|null,
     *     channel?: string|null,
     *     status?: string|null,
     *     date_from?: string|null,
     *     date_to?: string|null,
     * }  $filters
     * @return LengthAwarePaginator<int, Order>
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Order::query()
            ->with([
                'user:id,name,company_name,phone',
                'deliveryZone:id,name',
                'subscribeContract:id,user_id',
            ])
            ->withCount('items')
            ->latest('placed_at')
            ->latest('id');

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($q) use ($search): void {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['channel'])) {
            $query->where('channel', $filters['channel']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('placed_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('placed_at', '<=', $filters['date_to']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Order $order, bool $detailed = false): array
    {
        $order->loadMissing([
            'user:id,name,company_name,phone',
            'deliveryZone:id,name,fee',
            'subscribeContract.user:id,name,company_name',
            'subscriberSchedule:id,date,dish_title,persons',
        ]);

        $type = $order->type instanceof OrderType
            ? $order->type
            : OrderType::tryFrom((string) $order->type);
        $channel = $order->channel instanceof OrderChannel
            ? $order->channel
            : OrderChannel::tryFrom((string) $order->channel);
        $status = StatusRegistry::get('order', (string) $order->status);

        $payload = [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'user_id' => $order->user_id,
            'type' => $type?->value ?? (string) $order->type,
            'type_label' => $type?->label() ?? (string) $order->type,
            'channel' => $channel?->value ?? (string) $order->channel,
            'channel_label' => $channel?->label() ?? (string) $order->channel,
            'status' => $status['code'],
            'status_label' => $status['label'],
            'status_badge' => $status['badge'],
            'payment_method' => $order->payment_method,
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'customer_email' => $order->customer_email,
            'delivery_address' => $order->delivery_address,
            'delivery_zone_id' => $order->delivery_zone_id,
            'delivery_zone' => $order->deliveryZone?->name,
            'delivery_fee' => (float) $order->delivery_fee,
            'delivery_fee_formatted' => Money::format((float) $order->delivery_fee),
            'subtotal' => (float) $order->subtotal,
            'subtotal_formatted' => Money::format((float) $order->subtotal),
            'list_amount' => (float) $order->list_amount,
            'list_amount_formatted' => Money::format((float) $order->list_amount),
            'discount_amount' => (float) $order->discount_amount,
            'discount_amount_formatted' => Money::format((float) $order->discount_amount),
            'discount_percent' => (float) $order->discount_percent,
            'quoted_amount' => (float) $order->quoted_amount,
            'quoted_amount_formatted' => Money::format((float) $order->quoted_amount),
            'amount_paid' => $order->amount_paid !== null ? (float) $order->amount_paid : null,
            'amount_paid_formatted' => $order->amount_paid !== null
                ? Money::format((float) $order->amount_paid)
                : null,
            'subscribe_contract_id' => $order->subscribe_contract_id,
            'subscriber_company' => $order->subscribeContract?->user?->company_name
                ?: $order->subscribeContract?->user?->name,
            'subscriber_schedule_id' => $order->subscriber_schedule_id,
            'schedule_date' => $order->subscriberSchedule?->date?->toDateString(),
            'notes' => $order->notes,
            'delivery_person_name' => $order->delivery_person_name,
            'whatsapp_url' => $this->whatsappUrl($order->customer_phone),
            'items_count' => (int) ($order->items_count ?? $order->items()->count()),
            'can_edit' => $this->isManuallyEditable($order),
            'placed_at' => $order->placed_at?->toIso8601String(),
            'placed_at_formatted' => $order->placed_at?->format('d M Y H:i')
                ?? $order->created_at?->format('d M Y H:i'),
            'created_at' => $order->created_at?->toIso8601String(),
            'updated_at' => $order->updated_at?->toIso8601String(),
        ];

        if ($detailed) {
            $order->loadMissing([
                'items.addons',
                'statusHistories.changedBy:id,name',
            ]);

            $payload['items'] = $order->items->map(function (OrderItem $item) {
                return [
                    'id' => $item->id,
                    'dish_id' => $item->dish_id,
                    'dish_variation_id' => $item->dish_variation_id,
                    'title' => $item->title,
                    'variation_label' => $item->variation_label,
                    'quantity' => (int) $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'unit_price_formatted' => Money::format((float) $item->unit_price),
                    'line_total' => (float) $item->line_total,
                    'line_total_formatted' => Money::format((float) $item->line_total),
                    'addons' => $item->addons->map(fn (OrderItemAddon $addon) => [
                        'id' => $addon->id,
                        'addon_id' => $addon->addon_id,
                        'title' => $addon->title,
                        'quantity' => (int) $addon->quantity,
                        'unit_price_formatted' => Money::format((float) $addon->unit_price),
                        'line_total_formatted' => Money::format((float) $addon->line_total),
                    ])->values()->all(),
                ];
            })->values()->all();

            $payload['timeline'] = $order->statusHistories
                ->sortByDesc('id')
                ->values()
                ->map(function (OrderStatusHistory $row) {
                    $from = $row->from_status
                        ? StatusRegistry::get('order', $row->from_status)
                        : null;
                    $to = StatusRegistry::get('order', $row->to_status);

                    return [
                        'id' => $row->id,
                        'from_status' => $row->from_status,
                        'from_status_label' => $from['label'] ?? null,
                        'to_status' => $row->to_status,
                        'to_status_label' => $to['label'],
                        'to_status_badge' => $to['badge'],
                        'changed_by' => $row->changedBy?->name,
                        'note' => $row->note,
                        'created_at' => $row->created_at?->toIso8601String(),
                        'created_at_formatted' => $row->created_at?->format('d M Y H:i'),
                    ];
                })
                ->all();
        }

        return $payload;
    }

    public function updateStatus(
        Order $order,
        string $toStatus,
        ?User $actor = null,
        ?string $note = null,
        ?string $deliveryPersonName = null,
    ): Order {
        $allowed = StatusRegistry::codes('order');
        if (! in_array($toStatus, $allowed, true)) {
            throw new InvalidArgumentException('Invalid order status.');
        }

        if ($order->status === $toStatus) {
            throw new InvalidArgumentException('Order is already in that status.');
        }

        return DB::transaction(function () use ($order, $toStatus, $actor, $note, $deliveryPersonName): Order {
            $from = (string) $order->status;

            $order->status = $toStatus;
            if ($deliveryPersonName !== null) {
                $order->delivery_person_name = $deliveryPersonName !== ''
                    ? $deliveryPersonName
                    : null;
            }
            $order->save();

            OrderStatusHistory::query()->create([
                'order_id' => $order->id,
                'from_status' => $from,
                'to_status' => $toStatus,
                'changed_by' => $actor?->id,
                'note' => $note,
            ]);

            return $order->fresh([
                'user',
                'deliveryZone',
                'items.addons',
                'statusHistories.changedBy',
                'subscribeContract.user',
                'subscriberSchedule',
            ]);
        });
    }

    /**
     * Record initial/created status for orders created outside this service.
     */
    public function recordStatusChange(
        Order $order,
        ?string $fromStatus,
        string $toStatus,
        ?User $actor = null,
        ?string $note = null,
    ): void {
        OrderStatusHistory::query()->create([
            'order_id' => $order->id,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'changed_by' => $actor?->id,
            'note' => $note,
        ]);
    }

    /**
     * Manual admin order (bulk/event or walk-in individual).
     *
     * @param  array{
     *     type: string,
     *     status: string,
     *     customer_name: string,
     *     customer_phone?: string|null,
     *     customer_email?: string|null,
     *     delivery_address?: string|null,
     *     delivery_zone_id?: int|null,
     *     delivery_fee?: float|int|string|null,
     *     discount_amount?: float|int|string|null,
     *     discount_percent?: float|int|string|null,
     *     amount_paid?: float|int|string|null,
     *     notes?: string|null,
     *     delivery_person_name?: string|null,
     *     items: list<array{
     *         dish_variation_id: int,
     *         quantity: int,
     *         unit_price?: float|int|string|null,
     *         addons?: list<array{addon_id: int, quantity?: int}>|null
     *     }>,
     * }  $data
     */
    public function createManual(array $data, ?User $actor = null): Order
    {
        $type = OrderType::from($data['type']);
        if ($type === OrderType::Subscription) {
            throw new InvalidArgumentException('Subscription orders are created from schedules.');
        }

        return DB::transaction(function () use ($data, $actor, $type): Order {
            $built = $this->buildLineTotals($data['items'] ?? []);
            $pricing = $this->resolveOrderPricing($built['list_amount'], $data);

            $order = Order::query()->create([
                'order_number' => 'CC-A-'.Str::upper(Str::random(8)),
                'user_id' => null,
                'type' => $type,
                'channel' => OrderChannel::Admin,
                'status' => $data['status'],
                'payment_method' => 'cod',
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'] ?? null,
                'customer_email' => $data['customer_email'] ?? null,
                'delivery_address' => $data['delivery_address'] ?? null,
                'delivery_zone_id' => $data['delivery_zone_id'] ?? null,
                'delivery_fee' => $pricing['delivery_fee'],
                'subtotal' => $pricing['subtotal'],
                'list_amount' => $pricing['list_amount'],
                'discount_amount' => $pricing['discount_amount'],
                'discount_percent' => $pricing['discount_percent'],
                'quoted_amount' => $pricing['quoted_amount'],
                'notes' => $data['notes'] ?? null,
                'delivery_person_name' => $data['delivery_person_name'] ?? null,
                'placed_at' => now(),
            ]);

            if (array_key_exists('amount_paid', $data) && $data['amount_paid'] !== null && $data['amount_paid'] !== '') {
                $order->forceFill([
                    'amount_paid' => Money::of($data['amount_paid']),
                ])->save();
            }

            $this->syncItems($order, $built['lines']);

            OrderStatusHistory::query()->create([
                'order_id' => $order->id,
                'from_status' => null,
                'to_status' => $order->status,
                'changed_by' => $actor?->id,
                'note' => 'Manual order created',
            ]);

            return $order->load(['items.addons', 'deliveryZone'])->loadCount('items');
        });
    }

    /**
     * Public website COD checkout (individual / channel=web).
     *
     * @param  array{
     *     user_id: int,
     *     customer_name: string,
     *     customer_phone: string,
     *     customer_email?: string|null,
     *     delivery_address: string,
     *     delivery_zone_id: int,
     *     notes?: string|null,
     *     user_agent?: string|null,
     *     items: list<array{
     *         dish_variation_id: int,
     *         quantity: int,
     *         addons?: list<array{addon_id: int, quantity?: int}>|null
     *     }>,
     * }  $data
     */
    public function createWebCheckout(array $data): Order
    {
        return DB::transaction(function () use ($data): Order {
            $built = $this->buildLineTotals($data['items'] ?? []);
            $pricing = $this->resolveOrderPricing($built['list_amount'], [
                'delivery_zone_id' => $data['delivery_zone_id'],
            ]);

            $order = Order::query()->create([
                'order_number' => 'CC-W-'.Str::upper(Str::random(8)),
                'user_id' => $data['user_id'],
                'type' => OrderType::Individual,
                'channel' => OrderChannel::Web,
                'status' => 'pending',
                'payment_method' => 'cod',
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'customer_email' => $data['customer_email'] ?? null,
                'delivery_address' => $data['delivery_address'],
                'delivery_zone_id' => $data['delivery_zone_id'],
                'delivery_fee' => $pricing['delivery_fee'],
                'subtotal' => $pricing['subtotal'],
                'list_amount' => $pricing['list_amount'],
                'discount_amount' => $pricing['discount_amount'],
                'discount_percent' => $pricing['discount_percent'],
                'quoted_amount' => $pricing['quoted_amount'],
                'notes' => $data['notes'] ?? null,
                'client_platform' => 'web',
                'user_agent' => $data['user_agent'] ?? null,
                'placed_at' => now(),
            ]);

            $this->syncItems($order, $built['lines']);

            OrderStatusHistory::query()->create([
                'order_id' => $order->id,
                'from_status' => null,
                'to_status' => 'pending',
                'changed_by' => $data['user_id'],
                'note' => 'Website COD order placed',
            ]);

            return $order->load(['items.addons', 'deliveryZone'])->loadCount('items');
        });
    }

    /**
     * @param  array{
     *     type: string,
     *     status: string,
     *     customer_name: string,
     *     customer_phone?: string|null,
     *     customer_email?: string|null,
     *     delivery_address?: string|null,
     *     delivery_zone_id?: int|null,
     *     delivery_fee?: float|int|string|null,
     *     discount_amount?: float|int|string|null,
     *     discount_percent?: float|int|string|null,
     *     amount_paid?: float|int|string|null,
     *     notes?: string|null,
     *     delivery_person_name?: string|null,
     *     items: list<array{
     *         dish_variation_id: int,
     *         quantity: int,
     *         unit_price?: float|int|string|null,
     *         addons?: list<array{addon_id: int, quantity?: int}>|null
     *     }>,
     * }  $data
     */
    public function updateManual(Order $order, array $data, ?User $actor = null): Order
    {
        if (! $this->isManuallyEditable($order)) {
            throw new InvalidArgumentException('This order cannot be edited here.');
        }

        $type = OrderType::from($data['type']);
        if ($type === OrderType::Subscription) {
            throw new InvalidArgumentException('Cannot convert to a subscription order.');
        }

        return DB::transaction(function () use ($order, $data, $actor, $type): Order {
            $fromStatus = (string) $order->status;
            $built = $this->buildLineTotals($data['items'] ?? []);
            $pricing = $this->resolveOrderPricing($built['list_amount'], $data);

            $order->fill([
                'type' => $type,
                'status' => $data['status'],
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'] ?? null,
                'customer_email' => $data['customer_email'] ?? null,
                'delivery_address' => $data['delivery_address'] ?? null,
                'delivery_zone_id' => $data['delivery_zone_id'] ?? null,
                'delivery_fee' => $pricing['delivery_fee'],
                'subtotal' => $pricing['subtotal'],
                'list_amount' => $pricing['list_amount'],
                'discount_amount' => $pricing['discount_amount'],
                'discount_percent' => $pricing['discount_percent'],
                'quoted_amount' => $pricing['quoted_amount'],
                'notes' => $data['notes'] ?? null,
                'delivery_person_name' => $data['delivery_person_name'] ?? null,
            ])->save();

            if (array_key_exists('amount_paid', $data)) {
                $paid = $data['amount_paid'];
                $order->forceFill([
                    'amount_paid' => ($paid === null || $paid === '')
                        ? null
                        : Money::of($paid),
                ])->save();
            }

            $order->items()->each(function (OrderItem $item): void {
                $item->addons()->delete();
                $item->delete();
            });
            $this->syncItems($order, $built['lines']);

            if ($fromStatus !== $order->status) {
                OrderStatusHistory::query()->create([
                    'order_id' => $order->id,
                    'from_status' => $fromStatus,
                    'to_status' => $order->status,
                    'changed_by' => $actor?->id,
                    'note' => 'Updated via order edit',
                ]);
            }

            return $order->fresh([
                'items.addons',
                'deliveryZone',
                'statusHistories.changedBy',
            ])->loadCount('items');
        });
    }

    /**
     * @return list<array{value: int, label: string, fee: float, fee_formatted: string}>
     */
    public function zoneOptions(): array
    {
        return DeliveryZone::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'fee'])
            ->map(fn (DeliveryZone $zone) => [
                'value' => $zone->id,
                'label' => $zone->name,
                'fee' => (float) $zone->fee,
                'fee_formatted' => Money::format((float) $zone->fee),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{value: int, label: string, dish_id: int, dish_title: string, price: float, price_formatted: string, type: string}>
     */
    public function variationOptions(): array
    {
        return DishVariation::query()
            ->with('dish:id,title')
            ->where('status', 'active')
            ->whereHas('dish', fn ($q) => $q->where('status', 'active'))
            ->orderBy('sort_order')
            ->get()
            ->map(fn (DishVariation $variation) => [
                'value' => $variation->id,
                'label' => trim(($variation->dish?->title ?? 'Dish').' · '.$variation->label.' ('.$variation->quantity.' '.$variation->unit.')'),
                'dish_id' => $variation->dish_id,
                'dish_title' => $variation->dish?->title,
                'price' => (float) $variation->price,
                'price_formatted' => Money::format((float) $variation->price),
                'type' => $variation->type instanceof \App\Support\Enums\VariationType
                    ? $variation->type->value
                    : (string) $variation->type,
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{value: int, label: string, price: float, price_formatted: string}>
     */
    public function addonOptions(): array
    {
        return Addon::query()
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get(['id', 'title', 'price'])
            ->map(fn (Addon $addon) => [
                'value' => $addon->id,
                'label' => $addon->title,
                'price' => (float) $addon->price,
                'price_formatted' => Money::format((float) $addon->price),
            ])
            ->values()
            ->all();
    }

    /**
     * Manual create types only (no subscription).
     *
     * @return list<array{value: string, label: string}>
     */
    public function manualTypeOptions(): array
    {
        return [
            ['value' => OrderType::Event->value, 'label' => OrderType::Event->label()],
            ['value' => OrderType::Individual->value, 'label' => OrderType::Individual->label()],
        ];
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function typeOptions(): array
    {
        return array_map(
            fn (OrderType $type) => [
                'value' => $type->value,
                'label' => $type->label(),
            ],
            OrderType::cases(),
        );
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function channelOptions(): array
    {
        return array_map(
            fn (OrderChannel $channel) => [
                'value' => $channel->value,
                'label' => $channel->label(),
            ],
            OrderChannel::cases(),
        );
    }

    private function isManuallyEditable(Order $order): bool
    {
        $type = $order->type instanceof OrderType
            ? $order->type
            : OrderType::tryFrom((string) $order->type);

        return $type !== OrderType::Subscription
            && $order->subscriber_schedule_id === null;
    }

    /**
     * @param  list<array{dish_variation_id: int, quantity: int, unit_price?: float|int|string|null, addons?: list<array{addon_id: int, quantity?: int}>|null}>  $items
     * @return array{list_amount: float, lines: list<array<string, mixed>>}
     */
    private function buildLineTotals(array $items): array
    {
        if ($items === []) {
            throw new InvalidArgumentException('Add at least one line item.');
        }

        $listAmount = 0.0;
        $lines = [];

        foreach ($items as $row) {
            $variation = DishVariation::query()->with('dish')->findOrFail((int) $row['dish_variation_id']);
            $qty = max(1, (int) $row['quantity']);
            $unit = array_key_exists('unit_price', $row) && $row['unit_price'] !== null && $row['unit_price'] !== ''
                ? (float) $row['unit_price']
                : (float) $variation->price;
            $lineTotal = $unit * $qty;

            $addonRows = [];
            foreach ($row['addons'] ?? [] as $addonRow) {
                $addon = Addon::query()->findOrFail((int) $addonRow['addon_id']);
                $addonQty = max(1, (int) ($addonRow['quantity'] ?? 1));
                $addonUnit = (float) $addon->price;
                $addonTotal = $addonUnit * $addonQty;
                $lineTotal += $addonTotal;
                $addonRows[] = [
                    'addon_id' => $addon->id,
                    'title' => $addon->title,
                    'quantity' => $addonQty,
                    'unit_price' => Money::of($addonUnit),
                    'line_total' => Money::of($addonTotal),
                ];
            }

            $listAmount += $lineTotal;
            $lines[] = [
                'dish_id' => $variation->dish_id,
                'dish_variation_id' => $variation->id,
                'title' => $variation->dish?->title ?: $variation->label,
                'variation_label' => $variation->label.' ('.$variation->quantity.' '.$variation->unit.')',
                'quantity' => $qty,
                'unit_price' => Money::of($unit),
                'line_total' => Money::of($unit * $qty),
                'addons' => $addonRows,
            ];
        }

        return [
            'list_amount' => $listAmount,
            'lines' => $lines,
        ];
    }

    /**
     * @param  array{delivery_fee?: float|int|string|null, discount_amount?: float|int|string|null, discount_percent?: float|int|string|null, delivery_zone_id?: int|null}  $data
     * @return array{list_amount: string, discount_amount: string, discount_percent: string, delivery_fee: string, subtotal: string, quoted_amount: string}
     */
    private function resolveOrderPricing(float $listAmount, array $data): array
    {
        $deliveryFee = (float) ($data['delivery_fee'] ?? 0);
        if (! empty($data['delivery_zone_id']) && (! array_key_exists('delivery_fee', $data) || $data['delivery_fee'] === null || $data['delivery_fee'] === '')) {
            $zone = DeliveryZone::query()->find((int) $data['delivery_zone_id']);
            $deliveryFee = $zone ? (float) $zone->fee : 0.0;
        }

        $discountAmount = (float) ($data['discount_amount'] ?? 0);
        $discountPercent = (float) ($data['discount_percent'] ?? 0);
        if ($discountPercent > 0 && $discountAmount <= 0) {
            $discountAmount = (float) Money::percentOf($listAmount, $discountPercent);
        }

        if ($discountAmount > $listAmount) {
            throw new InvalidArgumentException('Discount cannot exceed list amount.');
        }

        $subtotal = $listAmount - $discountAmount;
        $quoted = $subtotal + $deliveryFee;

        return [
            'list_amount' => Money::of($listAmount),
            'discount_amount' => Money::of($discountAmount),
            'discount_percent' => Money::of($discountPercent),
            'delivery_fee' => Money::of($deliveryFee),
            'subtotal' => Money::of($subtotal),
            'quoted_amount' => Money::of($quoted),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     */
    private function syncItems(Order $order, array $lines): void
    {
        foreach ($lines as $line) {
            $item = OrderItem::query()->create([
                'order_id' => $order->id,
                'dish_id' => $line['dish_id'],
                'dish_variation_id' => $line['dish_variation_id'],
                'title' => $line['title'],
                'variation_label' => $line['variation_label'],
                'quantity' => $line['quantity'],
                'unit_price' => $line['unit_price'],
                'line_total' => $line['line_total'],
            ]);

            foreach ($line['addons'] as $addon) {
                OrderItemAddon::query()->create([
                    'order_item_id' => $item->id,
                    'addon_id' => $addon['addon_id'],
                    'title' => $addon['title'],
                    'quantity' => $addon['quantity'],
                    'unit_price' => $addon['unit_price'],
                    'line_total' => $addon['line_total'],
                ]);
            }
        }
    }

    private function whatsappUrl(?string $phone): ?string
    {
        if ($phone === null || trim($phone) === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            $digits = '92'.substr($digits, 1);
        } elseif (strlen($digits) === 10) {
            $digits = '92'.$digits;
        }

        return 'https://wa.me/'.$digits;
    }
}
