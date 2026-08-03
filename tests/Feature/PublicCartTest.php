<?php

use App\Modules\Addons\Models\Addon;
use App\Modules\Catalog\Models\Dish;
use App\Modules\Catalog\Models\DishVariation;
use App\Modules\PublicSite\Services\CartService;
use App\Support\Enums\VariationType;
use App\Support\Money;
use Database\Seeders\RolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

function makeCartableVariation(array $dish = [], array $variation = []): DishVariation
{
    $dishModel = Dish::factory()->create(array_merge([
        'title' => 'Chicken Biryani',
        'slug' => 'chicken-biryani',
        'status' => 'active',
    ], $dish));

    return DishVariation::factory()->create(array_merge([
        'dish_id' => $dishModel->id,
        'label' => '1 Serving',
        'type' => VariationType::Single,
        'status' => 'active',
        'price' => Money::of(650),
    ], $variation));
}

test('cart page shows empty state', function () {
    $this->get(route('cart'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/cart')
            ->where('empty', true)
            ->where('items_count', 0)
            ->where('subtotal_formatted', 'Rs. 0'));
});

test('can add single serving with addons to cart and see live totals', function () {
    $variation = makeCartableVariation();
    $raita = Addon::factory()->create([
        'title' => 'Raita',
        'price' => Money::of(80),
        'is_global' => true,
        'status' => 'active',
    ]);

    $this->post(route('cart.items.store'), [
        'dish_variation_id' => $variation->id,
        'quantity' => 2,
        'addon_ids' => [$raita->id],
    ])->assertRedirect();

    $this->get(route('cart'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/cart')
            ->where('empty', false)
            ->has('items', 1)
            ->where('items.0.dish_title', 'Chicken Biryani')
            ->where('items.0.quantity', 2)
            ->where('items.0.line_total_formatted', 'Rs. 1,460')
            ->where('subtotal_formatted', 'Rs. 1,460')
            ->has('items.0.addons', 1));

    $this->get(route('menu.show', 'chicken-biryani'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('cart.quantity', 2)
            ->where('cart.count', 1));
});

test('adding the same line merges quantities', function () {
    $variation = makeCartableVariation();

    $this->post(route('cart.items.store'), [
        'dish_variation_id' => $variation->id,
        'quantity' => 1,
    ]);
    $this->post(route('cart.items.store'), [
        'dish_variation_id' => $variation->id,
        'quantity' => 2,
    ]);

    $this->get(route('cart'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('items', 1)
            ->where('items.0.quantity', 3));
});

test('rejects bulk variations and inactive dishes', function () {
    $dish = Dish::factory()->create(['status' => 'active']);
    $bulk = DishVariation::factory()->bulk(5)->create([
        'dish_id' => $dish->id,
        'status' => 'active',
    ]);

    $this->post(route('cart.items.store'), [
        'dish_variation_id' => $bulk->id,
        'quantity' => 1,
    ])->assertSessionHasErrors('dish_variation_id');

    $inactiveDish = Dish::factory()->create(['status' => 'inactive']);
    $single = DishVariation::factory()->create([
        'dish_id' => $inactiveDish->id,
        'type' => VariationType::Single,
        'status' => 'active',
    ]);

    $this->post(route('cart.items.store'), [
        'dish_variation_id' => $single->id,
        'quantity' => 1,
    ])->assertSessionHasErrors('dish_variation_id');
});

test('can update quantity remove item and clear cart', function () {
    $variation = makeCartableVariation();
    $key = app(CartService::class)->lineKey($variation->id, []);

    $this->post(route('cart.items.store'), [
        'dish_variation_id' => $variation->id,
        'quantity' => 2,
    ]);

    $this->patch(route('cart.items.update'), [
        'key' => $key,
        'quantity' => 4,
    ])->assertRedirect(route('cart'));

    $this->get(route('cart'))
        ->assertInertia(fn (Assert $page) => $page->where('items.0.quantity', 4));

    $this->delete(route('cart.items.destroy'), ['key' => $key])
        ->assertRedirect(route('cart'));

    $this->get(route('cart'))
        ->assertInertia(fn (Assert $page) => $page->where('empty', true));

    $this->post(route('cart.items.store'), [
        'dish_variation_id' => $variation->id,
        'quantity' => 1,
    ]);

    $this->delete(route('cart.clear'))->assertRedirect(route('cart'));

    $this->get(route('cart'))
        ->assertInertia(fn (Assert $page) => $page->where('empty', true));
});

test('invalid cart lines are dropped when resolving', function () {
    $variation = makeCartableVariation();

    $this->post(route('cart.items.store'), [
        'dish_variation_id' => $variation->id,
        'quantity' => 1,
    ]);

    $variation->update(['status' => 'inactive']);

    $this->get(route('cart'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('empty', true)
            ->has('items', 0));
});
