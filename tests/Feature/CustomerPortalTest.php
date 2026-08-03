<?php

use App\Models\User;
use App\Modules\Addons\Models\Addon;
use App\Modules\Catalog\Models\Dish;
use App\Modules\Catalog\Models\DishVariation;
use App\Modules\Orders\Models\Order;
use App\Modules\Orders\Models\OrderItem;
use App\Modules\Orders\Models\OrderItemAddon;
use App\Modules\PublicSite\Services\CartService;
use App\Support\Enums\OrderChannel;
use App\Support\Enums\OrderType;
use App\Support\Enums\UserRole;
use App\Support\Enums\VariationType;
use App\Support\Money;
use Database\Seeders\RolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

function customerUser(): User
{
    $user = User::factory()->create([
        'email' => 'customer@example.com',
        'password' => 'password',
    ]);
    $user->assignRole(UserRole::Customer->value);

    return $user;
}

test('guests cannot open customer dashboard', function () {
    $this->get(route('customer.dashboard'))
        ->assertRedirect(route('login'));
});

test('customer dashboard shows stats and recent orders', function () {
    $user = customerUser();

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'order_number' => 'CC-W-CUST001',
        'channel' => OrderChannel::Web,
        'type' => OrderType::Individual,
        'status' => 'pending',
        'quoted_amount' => 1200,
        'placed_at' => now(),
    ]);

    OrderItem::query()->create([
        'order_id' => $order->id,
        'title' => 'Biryani',
        'variation_label' => '1 Serving',
        'quantity' => 1,
        'unit_price' => 600,
        'line_total' => 600,
    ]);

    $this->actingAs($user)
        ->get(route('customer.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('customer/dashboard')
            ->where('stats.my_orders_count', 1)
            ->where('stats.pending_count', 1)
            ->has('recent_orders', 1)
            ->where('recent_orders.0.order_number', 'CC-W-CUST001'));
});

test('non customer cannot open customer dashboard', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin->value);

    $this->actingAs($admin)
        ->get(route('customer.dashboard'))
        ->assertForbidden();
});

test('customer order history lists their orders only', function () {
    $user = customerUser();
    $other = User::factory()->create();
    $other->assignRole(UserRole::Customer->value);

    Order::factory()->create([
        'user_id' => $user->id,
        'order_number' => 'CC-W-MINE001',
    ]);
    Order::factory()->create([
        'user_id' => $other->id,
        'order_number' => 'CC-W-OTHER01',
    ]);

    $this->actingAs($user)
        ->get(route('customer.orders'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('customer/orders')
            ->has('orders.data', 1)
            ->where('orders.data.0.order_number', 'CC-W-MINE001'));
});

test('customer can reorder into cart', function () {
    $user = customerUser();

    $dish = Dish::factory()->create(['status' => 'active', 'title' => 'Karahi']);
    $variation = DishVariation::factory()->create([
        'dish_id' => $dish->id,
        'type' => VariationType::Single,
        'status' => 'active',
        'price' => Money::of(500),
        'label' => '1 Serving',
    ]);
    $raita = Addon::factory()->create([
        'title' => 'Raita',
        'price' => Money::of(50),
        'is_global' => true,
        'status' => 'active',
    ]);

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'order_number' => 'CC-W-REORD01',
    ]);

    $item = OrderItem::query()->create([
        'order_id' => $order->id,
        'dish_id' => $dish->id,
        'dish_variation_id' => $variation->id,
        'title' => 'Karahi',
        'variation_label' => '1 Serving',
        'quantity' => 2,
        'unit_price' => 500,
        'line_total' => 1000,
    ]);

    OrderItemAddon::query()->create([
        'order_item_id' => $item->id,
        'addon_id' => $raita->id,
        'title' => 'Raita',
        'quantity' => 2,
        'unit_price' => 50,
        'line_total' => 100,
    ]);

    $this->actingAs($user)
        ->post(route('customer.orders.reorder', $order))
        ->assertRedirect(route('cart'));

    $cart = app(CartService::class)->payload();
    expect($cart['empty'])->toBeFalse()
        ->and($cart['items_count'])->toBe(1)
        ->and($cart['items'][0]['quantity'])->toBe(2)
        ->and($cart['items'][0]['addons'])->toHaveCount(1);
});

test('customer cannot reorder another users order', function () {
    $user = customerUser();
    $other = User::factory()->create();
    $other->assignRole(UserRole::Customer->value);

    $order = Order::factory()->create(['user_id' => $other->id]);

    $this->actingAs($user)
        ->post(route('customer.orders.reorder', $order))
        ->assertForbidden();
});

test('customer login redirects to customer dashboard', function () {
    $user = customerUser();

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('customer.dashboard'));

    $this->assertAuthenticatedAs($user);
});
