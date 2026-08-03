<?php

namespace App\Modules\PublicSite\Services;

use App\Models\User;
use App\Modules\Delivery\Models\DeliveryZone;
use App\Modules\Orders\Models\Order;
use App\Modules\Orders\Services\OrderService;
use App\Support\Enums\UserRole;
use App\Support\ErrorCatalog;
use App\Support\StatusRegistry;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(
        private readonly CartService $cart,
        private readonly OrderService $orders,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function payload(?User $user = null): array
    {
        $cart = $this->cart->payload();
        $zones = DeliveryZone::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'fee'])
            ->map(fn (DeliveryZone $zone) => [
                'id' => $zone->id,
                'name' => $zone->name,
                'fee' => (float) $zone->fee,
                'fee_formatted' => $this->formatRs((float) $zone->fee),
            ])
            ->values()
            ->all();

        return [
            ...$cart,
            'zones' => $zones,
            'payment_method' => 'cod',
            'customer' => [
                'name' => $user?->name ?? '',
                'phone' => $user?->phone ?? '',
                'email' => $user?->email ?? '',
                'company_name' => $user?->company_name ?? '',
                'delivery_address' => $user?->address ?? '',
            ],
        ];
    }

    /**
     * @param  array{
     *     customer_name: string,
     *     customer_phone: string,
     *     customer_email: string,
     *     company_name?: string|null,
     *     delivery_address: string,
     *     delivery_zone_id: int,
     *     notes?: string|null,
     *     user_agent?: string|null,
     * }  $data
     */
    public function place(array $data, ?User $actor = null): Order
    {
        $cart = $this->cart->payload();

        if ($cart['empty']) {
            throw ValidationException::withMessages([
                'cart' => ErrorCatalog::message('cart_empty'),
            ]);
        }

        $zone = DeliveryZone::query()
            ->active()
            ->whereKey($data['delivery_zone_id'])
            ->first();

        if (! $zone) {
            throw ValidationException::withMessages([
                'delivery_zone_id' => ErrorCatalog::message('zone_required'),
            ]);
        }

        $items = collect($cart['items'])->map(function (array $item) {
            $qty = (int) $item['quantity'];

            return [
                'dish_variation_id' => (int) $item['variation_id'],
                'quantity' => $qty,
                'addons' => collect($item['addons'] ?? [])->map(fn (array $addon) => [
                    'addon_id' => (int) $addon['id'],
                    'quantity' => $qty,
                ])->all(),
            ];
        })->values()->all();

        $wasGuest = ! Auth::check();

        $order = DB::transaction(function () use ($data, $actor, $items, $zone): Order {
            $user = $this->resolveCustomer($data, $actor);

            $order = $this->orders->createWebCheckout([
                'user_id' => $user->id,
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'customer_email' => $data['customer_email'],
                'delivery_address' => $data['delivery_address'],
                'delivery_zone_id' => $zone->id,
                'notes' => $data['notes'] ?? null,
                'user_agent' => $data['user_agent'] ?? null,
                'items' => $items,
            ]);

            $this->cart->clear();

            return $order->setRelation('user', $user);
        });

        // Silent session login only — navigation stays on checkout → order success
        // (never Fortify home /dashboard). Already-authenticated users stay as-is.
        if ($wasGuest && $order->user instanceof User) {
            Auth::login($order->user);
            session()->forget('url.intended');
        }

        return $order;
    }

    /**
     * Email is the account key: existing email → reuse user; new email → create customer.
     *
     * @param  array{
     *     customer_name: string,
     *     customer_phone: string,
     *     customer_email: string,
     *     company_name?: string|null,
     *     delivery_address: string,
     * }  $data
     */
    private function resolveCustomer(array $data, ?User $actor): User
    {
        $email = strtolower(trim($data['customer_email']));
        $phone = trim($data['customer_phone']);

        $existing = User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if ($existing instanceof User) {
            return $this->syncCustomerProfile($existing, $data, $phone);
        }

        // Logged-in user with no email (or matching blank) can claim this new email.
        if ($actor instanceof User && blank($actor->email)) {
            $actor->forceFill([
                'email' => $email,
            ])->save();

            return $this->syncCustomerProfile($actor->fresh(), $data, $phone);
        }

        $phoneOwner = User::query()->where('phone', $phone)->first();
        if ($phoneOwner instanceof User) {
            throw ValidationException::withMessages([
                'customer_phone' => 'This phone is already registered with another account. Use that account’s email, or a different phone.',
            ]);
        }

        $user = User::query()->create([
            'name' => $data['customer_name'],
            'email' => $email,
            'phone' => $phone,
            'password' => $phone,
            'company_name' => $data['company_name'] ?? null,
            'address' => $data['delivery_address'],
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $user->assignRole(UserRole::Customer->value);

        return $user;
    }

    /**
     * @param  array{
     *     customer_name: string,
     *     customer_phone: string,
     *     customer_email: string,
     *     company_name?: string|null,
     *     delivery_address: string,
     * }  $data
     */
    private function syncCustomerProfile(User $user, array $data, string $phone): User
    {
        $phoneOwner = User::query()
            ->where('phone', $phone)
            ->whereKeyNot($user->id)
            ->first();

        $user->forceFill([
            'name' => $data['customer_name'],
            'email' => strtolower(trim($data['customer_email'])),
            'phone' => $phoneOwner ? $user->phone : $phone,
            'company_name' => $data['company_name'] ?? $user->company_name,
            'address' => $data['delivery_address'],
        ])->save();

        if (! $user->hasRole(UserRole::Customer->value) && ! $user->roles()->exists()) {
            $user->assignRole(UserRole::Customer->value);
        }

        return $user->fresh();
    }

    /**
     * @return array<string, mixed>
     */
    public function successPayload(string $orderNumber): array
    {
        $order = Order::query()
            ->where('order_number', $orderNumber)
            ->where('channel', 'web')
            ->with(['deliveryZone:id,name,fee', 'items.addons'])
            ->firstOrFail();

        $items = $order->items->map(function ($item): array {
            $addons = $item->addons->map(fn ($addon): array => [
                'id' => $addon->id,
                'title' => $addon->title,
                'quantity' => (int) $addon->quantity,
                'unit_price_formatted' => $this->formatRs((float) $addon->unit_price),
                'line_total_formatted' => $this->formatRs((float) $addon->line_total),
            ])->values()->all();

            $addonsTotal = (float) $item->addons->sum(fn ($addon) => (float) $addon->line_total);
            $lineTotal = (float) $item->line_total + $addonsTotal;

            return [
                'id' => $item->id,
                'title' => $item->title,
                'variation_label' => $item->variation_label,
                'quantity' => (int) $item->quantity,
                'unit_price_formatted' => $this->formatRs((float) $item->unit_price),
                'line_total_formatted' => $this->formatRs($lineTotal),
                'addons' => $addons,
            ];
        })->values()->all();

        return [
            'order' => [
                'order_number' => $order->order_number,
                'status' => $order->status,
                'payment_method' => $order->payment_method,
                'payment_label' => 'Cash on delivery',
                'customer_name' => $order->customer_name,
                'customer_phone' => $order->customer_phone,
                'customer_email' => $order->customer_email,
                'delivery_address' => $order->delivery_address,
                'delivery_zone' => $order->deliveryZone?->name,
                'notes' => $order->notes,
                'delivery_fee_formatted' => $this->formatRs((float) $order->delivery_fee),
                'subtotal_formatted' => $this->formatRs((float) $order->subtotal),
                'total' => (float) $order->quoted_amount,
                'total_formatted' => $this->formatRs((float) $order->quoted_amount),
                'items_count' => $order->items->count(),
                'items' => $items,
                'status_label' => StatusRegistry::label('order', (string) $order->status),
                'status_badge' => StatusRegistry::get('order', (string) $order->status)['badge'],
                'placed_at' => $order->placed_at?->toIso8601String(),
                'placed_at_formatted' => $order->placed_at?->timezone(config('app.timezone'))->format('d M Y · h:i A'),
            ],
        ];
    }

    private function formatRs(float $amount): string
    {
        return 'Rs. '.number_format($amount, 0);
    }
}
