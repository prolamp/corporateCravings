<?php

use App\Models\User;
use App\Modules\Catalog\Models\DishVariation;
use App\Modules\Delivery\Models\DeliveryZone;
use App\Modules\Orders\Models\Order;
use App\Modules\Orders\Models\OrderItem;
use App\Modules\Orders\Models\OrderStatusHistory;
use App\Support\Enums\OrderChannel;
use App\Support\Enums\OrderType;
use App\Support\Enums\UserRole;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

function actingAsOrderStaff(UserRole $role = UserRole::Admin): User
{
    $user = User::factory()->create(['status' => 'active']);
    $user->assignRole($role->value);

    return $user;
}

test('guests cannot access orders', function () {
    $this->get(route('admin.orders.index'))->assertRedirect(route('login'));
});

test('assistant can list and view orders but cannot manage', function () {
    $assistant = actingAsOrderStaff(UserRole::Assistant);
    $order = Order::factory()->create([
        'status' => 'pending',
        'type' => OrderType::Individual,
        'channel' => OrderChannel::Web,
    ]);

    $this->actingAs($assistant)
        ->get(route('admin.orders.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/orders/index')
            ->where('canUpdateStatus', true)
            ->where('canManage', false));

    $this->actingAs($assistant)
        ->get(route('admin.orders.show', $order))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('admin/orders/show'));
});

test('admin can filter orders by type and status', function () {
    $admin = actingAsOrderStaff();
    Order::factory()->create([
        'type' => OrderType::Individual,
        'status' => 'pending',
        'order_number' => 'CC-WEB-AAA',
    ]);
    Order::factory()->event()->create([
        'status' => 'confirmed',
        'order_number' => 'CC-EVT-BBB',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.orders.index', [
            'type' => 'event',
            'status' => 'confirmed',
        ]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('orders.data', 1)
            ->where('orders.data.0.order_number', 'CC-EVT-BBB'));
});

test('admin can update order status and append timeline', function () {
    $admin = actingAsOrderStaff();
    $order = Order::factory()->create(['status' => 'pending']);
    OrderItem::query()->create([
        'order_id' => $order->id,
        'title' => 'Chicken Biryani',
        'quantity' => 2,
        'unit_price' => 500,
        'line_total' => 1000,
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.orders.update-status', $order), [
            'status' => 'confirmed',
            'note' => 'Called customer',
            'delivery_person_name' => 'Ali',
        ])
        ->assertRedirect();

    $fresh = $order->fresh();
    expect($fresh->status)->toBe('confirmed')
        ->and($fresh->delivery_person_name)->toBe('Ali');

    expect(OrderStatusHistory::query()->where('order_id', $order->id)->count())->toBe(1);

    $this->actingAs($admin)
        ->get(route('admin.orders.show', $order))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('order.status', 'confirmed')
            ->has('order.items', 1)
            ->has('order.timeline', 1)
            ->where('order.timeline.0.note', 'Called customer'));
});

test('same status update is rejected', function () {
    $admin = actingAsOrderStaff();
    $order = Order::factory()->create(['status' => 'pending']);

    $this->actingAs($admin)
        ->from(route('admin.orders.show', $order))
        ->patch(route('admin.orders.update-status', $order), [
            'status' => 'pending',
        ])
        ->assertRedirect(route('admin.orders.show', $order))
        ->assertSessionHas('error');
});

test('assistant cannot open create order form', function () {
    $assistant = actingAsOrderStaff(UserRole::Assistant);

    $this->actingAs($assistant)
        ->get(route('admin.orders.create'))
        ->assertForbidden();
});

test('admin can create a manual event order', function () {
    $admin = actingAsOrderStaff();
    $variation = DishVariation::factory()->create(['price' => 1000, 'status' => 'active']);
    $zone = DeliveryZone::factory()->create(['fee' => 150, 'is_active' => true]);

    $this->actingAs($admin)
        ->get(route('admin.orders.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/orders/create')
            ->has('variationOptions')
            ->has('zoneOptions'));

    $this->actingAs($admin)
        ->post(route('admin.orders.store'), [
            'type' => OrderType::Event->value,
            'status' => 'pending',
            'customer_name' => 'Event Client',
            'customer_phone' => '03001234567',
            'delivery_zone_id' => $zone->id,
            'delivery_fee' => 150,
            'discount_amount' => 200,
            'discount_percent' => 0,
            'amount_paid' => 950,
            'items' => [
                [
                    'dish_variation_id' => $variation->id,
                    'quantity' => 1,
                    'unit_price' => 1000,
                    'addons' => [],
                ],
            ],
        ])
        ->assertRedirect();

    $order = Order::query()->where('customer_name', 'Event Client')->first();
    expect($order)->not->toBeNull()
        ->and($order->type)->toBe(OrderType::Event)
        ->and($order->channel)->toBe(OrderChannel::Admin)
        ->and((float) $order->list_amount)->toBe(1000.0)
        ->and((float) $order->discount_amount)->toBe(200.0)
        ->and((float) $order->delivery_fee)->toBe(150.0)
        ->and((float) $order->quoted_amount)->toBe(950.0)
        ->and((float) $order->amount_paid)->toBe(950.0)
        ->and($order->items()->count())->toBe(1);

    expect(OrderStatusHistory::query()->where('order_id', $order->id)->count())->toBe(1);
});

test('admin can edit a manual order', function () {
    $admin = actingAsOrderStaff();
    $variation = DishVariation::factory()->create(['price' => 500, 'status' => 'active']);
    $order = Order::factory()->create([
        'type' => OrderType::Individual,
        'channel' => OrderChannel::Admin,
        'status' => 'pending',
        'customer_name' => 'Walk-in',
        'list_amount' => 500,
        'discount_amount' => 0,
        'subtotal' => 500,
        'quoted_amount' => 500,
        'subscriber_schedule_id' => null,
    ]);
    OrderItem::query()->create([
        'order_id' => $order->id,
        'dish_variation_id' => $variation->id,
        'title' => 'Test dish',
        'quantity' => 1,
        'unit_price' => 500,
        'line_total' => 500,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.orders.edit', $order))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/orders/edit')
            ->where('order.can_edit', true));

    $this->actingAs($admin)
        ->put(route('admin.orders.update', $order), [
            'type' => OrderType::Individual->value,
            'status' => 'confirmed',
            'customer_name' => 'Walk-in Updated',
            'discount_amount' => 50,
            'discount_percent' => 0,
            'delivery_fee' => 0,
            'items' => [
                [
                    'dish_variation_id' => $variation->id,
                    'quantity' => 2,
                    'unit_price' => 500,
                    'addons' => [],
                ],
            ],
        ])
        ->assertRedirect(route('admin.orders.show', $order));

    $fresh = $order->fresh();
    expect($fresh->customer_name)->toBe('Walk-in Updated')
        ->and($fresh->status)->toBe('confirmed')
        ->and((float) $fresh->list_amount)->toBe(1000.0)
        ->and((float) $fresh->discount_amount)->toBe(50.0)
        ->and((float) $fresh->quoted_amount)->toBe(950.0)
        ->and($fresh->items()->count())->toBe(1)
        ->and((int) $fresh->items()->first()->quantity)->toBe(2);
});

test('subscription orders cannot be edited via manual form', function () {
    $admin = actingAsOrderStaff();
    $order = Order::factory()->create([
        'type' => OrderType::Subscription,
        'status' => 'pending',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.orders.edit', $order))
        ->assertForbidden();
});
