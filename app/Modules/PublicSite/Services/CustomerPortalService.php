<?php

namespace App\Modules\PublicSite\Services;

use App\Models\User;
use App\Modules\Orders\Models\Order;
use App\Modules\Orders\Models\OrderItem;
use App\Support\ErrorCatalog;
use App\Support\StatusRegistry;
use Illuminate\Validation\ValidationException;

class CustomerPortalService
{
    public function __construct(
        private readonly CartService $cart,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function dashboard(User $user): array
    {
        $ordersQuery = Order::query()->where('user_id', $user->id);

        $recent = (clone $ordersQuery)
            ->with(['items:id,order_id', 'deliveryZone:id,name'])
            ->latest('placed_at')
            ->latest('id')
            ->limit(5)
            ->get()
            ->map(fn (Order $order): array => $this->presentOrderSummary($order))
            ->values()
            ->all();

        return [
            'stats' => [
                'my_orders_count' => (clone $ordersQuery)->count(),
                'pending_count' => (clone $ordersQuery)->whereIn('status', ['pending', 'confirmed', 'leave_for_delivery'])->count(),
                'completed_count' => (clone $ordersQuery)->whereIn('status', ['delivered', 'completed'])->count(),
            ],
            'recent_orders' => $recent,
            'links' => [
                'orders' => $user->can('portal.customer.orders'),
                'track' => $user->can('portal.customer.track'),
                'history' => $user->can('portal.customer.history'),
            ],
        ];
    }

    /**
     * @return array{orders: array{data: list<array<string, mixed>>, meta: array<string, mixed>}}
     */
    public function orders(User $user): array
    {
        $paginator = Order::query()
            ->where('user_id', $user->id)
            ->with(['items:id,order_id', 'deliveryZone:id,name'])
            ->latest('placed_at')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return [
            'orders' => [
                'data' => $paginator->getCollection()
                    ->map(fn (Order $order): array => $this->presentOrderSummary($order))
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
     * @return array{added: int, skipped: int}
     */
    public function reorder(User $user, Order $order): array
    {
        if ((int) $order->user_id !== (int) $user->id) {
            abort(403);
        }

        $order->loadMissing(['items.addons']);

        $added = 0;
        $skipped = 0;

        foreach ($order->items as $item) {
            if ($this->addLineToCart($item)) {
                $added++;
            } else {
                $skipped++;
            }
        }

        if ($added === 0) {
            throw ValidationException::withMessages([
                'order' => ErrorCatalog::message(
                    'order_reorder_failed',
                    'None of the items from this order are available to reorder.',
                ),
            ]);
        }

        return [
            'added' => $added,
            'skipped' => $skipped,
        ];
    }

    private function addLineToCart(OrderItem $item): bool
    {
        $variationId = (int) ($item->dish_variation_id ?? 0);
        if ($variationId < 1) {
            return false;
        }

        $addonIds = $item->addons
            ->pluck('addon_id')
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->values()
            ->all();

        try {
            $this->cart->add($variationId, (int) $item->quantity, $addonIds);

            return true;
        } catch (ValidationException) {
            return false;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function presentOrderSummary(Order $order): array
    {
        $status = StatusRegistry::get('order', (string) $order->status);

        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $status['code'],
            'status_label' => $status['label'],
            'status_badge' => $status['badge'],
            'items_count' => $order->items->count(),
            'total_formatted' => 'Rs. '.number_format((float) $order->quoted_amount, 0),
            'delivery_zone' => $order->deliveryZone?->name,
            'placed_at_formatted' => $order->placed_at
                ? $order->placed_at->timezone(config('app.timezone'))->format('d M Y · h:i A')
                : null,
            'can_reorder' => $order->items->isNotEmpty(),
            'track_href' => '/track-order?order='.urlencode($order->order_number),
        ];
    }
}
