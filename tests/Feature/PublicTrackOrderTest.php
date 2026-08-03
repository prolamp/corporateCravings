<?php

use App\Modules\Orders\Models\Order;
use App\Modules\Orders\Models\OrderItem;
use App\Modules\Orders\Models\OrderStatusHistory;
use App\Support\Enums\OrderChannel;
use App\Support\Enums\OrderType;
use App\Support\ErrorCatalog;
use Database\Seeders\RolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('track order page loads empty form', function () {
    $this->get(route('track-order'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/track-order')
            ->where('query', '')
            ->where('error', null)
            ->where('order', null));
});

test('track order finds order by number with progress and timeline', function () {
    $order = Order::factory()->create([
        'order_number' => 'CC-W-TRACK01',
        'channel' => OrderChannel::Web,
        'type' => OrderType::Individual,
        'status' => 'confirmed',
        'customer_name' => 'Sara Ali',
        'customer_phone' => '03005551234',
        'delivery_address' => 'Street 9, F-8',
        'quoted_amount' => 1500,
        'payment_method' => 'cod',
        'placed_at' => now(),
    ]);

    OrderItem::query()->create([
        'order_id' => $order->id,
        'dish_id' => null,
        'dish_variation_id' => null,
        'title' => 'Chicken Biryani',
        'variation_label' => '1 Serving (1 serving)',
        'quantity' => 2,
        'unit_price' => 600,
        'line_total' => 1200,
    ]);

    OrderStatusHistory::query()->create([
        'order_id' => $order->id,
        'from_status' => null,
        'to_status' => 'pending',
        'changed_by' => $order->user_id,
        'note' => 'Placed',
    ]);

    OrderStatusHistory::query()->create([
        'order_id' => $order->id,
        'from_status' => 'pending',
        'to_status' => 'confirmed',
        'changed_by' => $order->user_id,
        'note' => 'Confirmed by kitchen',
    ]);

    $this->get(route('track-order', ['order' => 'cc-w-track01']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/track-order')
            ->where('query', 'CC-W-TRACK01')
            ->where('error', null)
            ->where('order.order_number', 'CC-W-TRACK01')
            ->where('order.status', 'confirmed')
            ->where('order.status_label', 'Confirmed')
            ->where('order.customer_name', 'Sara Ali')
            ->where('order.delivery_address', 'Street 9, F-8')
            ->where('order.total_formatted', 'Rs. 1,500')
            ->has('order.items', 1)
            ->where('order.items.0.title', 'Chicken Biryani')
            ->where('order.items.0.quantity', 2)
            ->has('order.timeline', 2)
            ->has('order.progress', 5)
            ->where('order.progress.0.state', 'done')
            ->where('order.progress.1.state', 'current')
            ->where('order.progress.1.code', 'confirmed'));
});

test('track order shows catalog message when not found', function () {
    $this->get(route('track-order', ['order' => 'CC-W-MISSING']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/track-order')
            ->where('query', 'CC-W-MISSING')
            ->where('error', ErrorCatalog::message('order_not_found'))
            ->where('order', null));
});

test('track order cancelled shows cancelled progress only', function () {
    $order = Order::factory()->create([
        'order_number' => 'CC-W-CANCEL1',
        'status' => 'cancelled',
        'channel' => OrderChannel::Web,
    ]);

    OrderStatusHistory::query()->create([
        'order_id' => $order->id,
        'from_status' => 'pending',
        'to_status' => 'cancelled',
        'changed_by' => $order->user_id,
    ]);

    $this->get(route('track-order', ['order' => 'CC-W-CANCEL1']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('order.status', 'cancelled')
            ->has('order.progress', 1)
            ->where('order.progress.0.code', 'cancelled')
            ->where('order.progress.0.state', 'cancelled'));
});
