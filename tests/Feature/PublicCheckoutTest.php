<?php

use App\Models\User;
use App\Modules\Addons\Models\Addon;
use App\Modules\Catalog\Models\Dish;
use App\Modules\Catalog\Models\DishVariation;
use App\Modules\Delivery\Models\DeliveryZone;
use App\Modules\Orders\Models\Order;
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

function seedCartableLine(array $addonIds = []): DishVariation
{
    $dish = Dish::factory()->create(['status' => 'active', 'title' => 'Chicken Biryani']);
    $variation = DishVariation::factory()->create([
        'dish_id' => $dish->id,
        'type' => VariationType::Single,
        'status' => 'active',
        'price' => Money::of(600),
        'label' => '1 Serving',
    ]);

    app(CartService::class)->add($variation->id, 2, $addonIds);

    return $variation;
}

test('checkout redirects to cart when empty', function () {
    $this->get(route('checkout'))
        ->assertRedirect(route('cart'));
});

test('checkout page shows cart lines zones and cod only', function () {
    seedCartableLine();
    DeliveryZone::factory()->create(['name' => 'I-9', 'fee' => 150, 'is_active' => true]);
    DeliveryZone::factory()->inactive()->create(['name' => 'Hidden']);

    $this->get(route('checkout'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/checkout')
            ->has('items', 1)
            ->where('items.0.quantity', 2)
            ->where('subtotal_formatted', 'Rs. 1,200')
            ->has('zones', 1)
            ->where('zones.0.name', 'I-9')
            ->where('zones.0.fee_formatted', 'Rs. 150')
            ->where('payment_method', 'cod'));
});

test('placing checkout creates web pending order customer and clears cart', function () {
    $variation = seedCartableLine();
    $raita = Addon::factory()->create([
        'title' => 'Raita',
        'price' => Money::of(80),
        'is_global' => true,
        'status' => 'active',
    ]);
    app(CartService::class)->clear();
    app(CartService::class)->add($variation->id, 2, [$raita->id]);

    $zone = DeliveryZone::factory()->create([
        'name' => 'F-7',
        'fee' => 200,
        'is_active' => true,
    ]);

    $response = $this->post(route('checkout.store'), [
        'customer_name' => 'Ali Khan',
        'customer_phone' => '03001234567',
        'customer_email' => 'Ali@Example.com',
        'company_name' => 'Acme Corp',
        'delivery_address' => 'Street 4, F-7',
        'delivery_zone_id' => $zone->id,
        'notes' => 'Call on arrival',
    ]);

    $order = Order::query()->first();
    expect($order)->not->toBeNull();

    $response
        ->assertRedirect(route('orders.success', $order->order_number))
        ->assertSessionMissing('url.intended');

    expect($response->headers->get('Location'))
        ->not->toContain('/dashboard')
        ->and($response->headers->get('Location'))
        ->not->toContain('/customer/dashboard');

    expect($order->channel)->toBe(OrderChannel::Web)
        ->and($order->type)->toBe(OrderType::Individual)
        ->and($order->status)->toBe('pending')
        ->and($order->payment_method)->toBe('cod')
        ->and((float) $order->delivery_fee)->toBe(200.0)
        ->and((float) $order->subtotal)->toBe(1360.0)
        ->and((float) $order->quoted_amount)->toBe(1560.0)
        ->and($order->order_number)->toStartWith('CC-W-');

    $user = User::query()->where('email', 'ali@example.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->hasRole(UserRole::Customer->value))->toBeTrue()
        ->and($order->user_id)->toBe($user->id);

    $this->assertAuthenticatedAs($user);

    expect(app(CartService::class)->payload()['empty'])->toBeTrue();

    $this->get(route('orders.success', $order->order_number))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/order-success')
            ->where('order.order_number', $order->order_number)
            ->where('order.total_formatted', 'Rs. 1,560')
            ->where('order.delivery_address', 'Street 4, F-7')
            ->where('order.delivery_zone', 'F-7')
            ->has('order.items', 1)
            ->where('order.items.0.quantity', 2)
            ->where('order.items.0.title', 'Chicken Biryani')
            ->has('order.items.0.addons', 1)
            ->where('order.items.0.addons.0.title', 'Raita'));
});

test('checkout reuses existing customer by email without unique error', function () {
    seedCartableLine();
    $zone = DeliveryZone::factory()->create(['fee' => 0, 'is_active' => true]);

    $existing = User::factory()->create([
        'email' => 'returning@example.com',
        'phone' => '03009876543',
        'name' => 'Old Name',
    ]);
    $existing->assignRole(UserRole::Customer->value);

    $this->post(route('checkout.store'), [
        'customer_name' => 'New Name',
        'customer_phone' => '03001110000',
        'customer_email' => 'Returning@Example.com',
        'delivery_address' => 'Office 12',
        'delivery_zone_id' => $zone->id,
    ])
        ->assertRedirect(route('orders.success', Order::query()->first()->order_number))
        ->assertSessionMissing('url.intended');

    expect(User::query()->whereRaw('LOWER(email) = ?', ['returning@example.com'])->count())->toBe(1)
        ->and(Order::query()->first()->user_id)->toBe($existing->id)
        ->and($existing->fresh()->name)->toBe('New Name')
        ->and($existing->fresh()->phone)->toBe('03001110000');

    $this->assertAuthenticatedAs($existing->fresh());
});

test('logged in customer stays logged in without re-login', function () {
    seedCartableLine();
    $zone = DeliveryZone::factory()->create(['fee' => 100, 'is_active' => true]);

    $customer = User::factory()->create([
        'email' => 'logged@example.com',
        'phone' => '03002223344',
        'name' => 'Logged Customer',
    ]);
    $customer->assignRole(UserRole::Customer->value);

    $this->actingAs($customer)
        ->post(route('checkout.store'), [
            'customer_name' => 'Logged Customer',
            'customer_phone' => '03002223344',
            'customer_email' => 'logged@example.com',
            'delivery_address' => 'Home',
            'delivery_zone_id' => $zone->id,
        ])
        ->assertRedirect();

    expect(Order::query()->first()->user_id)->toBe($customer->id);
    $this->assertAuthenticatedAs($customer);
});

test('checkout rejects inactive zone and empty cart post', function () {
    seedCartableLine();
    $inactive = DeliveryZone::factory()->inactive()->create();

    $this->post(route('checkout.store'), [
        'customer_name' => 'Ali',
        'customer_phone' => '03001112233',
        'customer_email' => 'ali2@example.com',
        'delivery_address' => 'Somewhere',
        'delivery_zone_id' => $inactive->id,
    ])->assertSessionHasErrors('delivery_zone_id');

    app(CartService::class)->clear();

    $zone = DeliveryZone::factory()->create(['is_active' => true]);
    $this->post(route('checkout.store'), [
        'customer_name' => 'Ali',
        'customer_phone' => '03001112233',
        'customer_email' => 'ali2@example.com',
        'delivery_address' => 'Somewhere',
        'delivery_zone_id' => $zone->id,
    ])->assertRedirect(route('cart'));
});

test('checkout requires email for customer registration', function () {
    seedCartableLine();
    $zone = DeliveryZone::factory()->create(['is_active' => true]);

    $this->post(route('checkout.store'), [
        'customer_name' => 'Ali Khan',
        'customer_phone' => '03005556677',
        'delivery_address' => 'Street 1',
        'delivery_zone_id' => $zone->id,
    ])->assertSessionHasErrors('customer_email');
});
