<?php

use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Dish;
use App\Modules\Catalog\Models\DishVariation;
use App\Support\Enums\VariationType;
use App\Support\Money;
use Database\Seeders\RolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('bulk orders page lists active dishes with kg tiers and category chips', function () {
    $rice = Category::factory()->create(['name' => 'Rice', 'slug' => 'rice', 'status' => 'active']);
    $chicken = Category::factory()->create(['name' => 'Chicken', 'slug' => 'chicken', 'status' => 'active']);

    $biryani = Dish::factory()->create([
        'category_id' => $rice->id,
        'title' => 'Chicken Biryani',
        'status' => 'active',
        'sort_order' => 1,
    ]);
    DishVariation::factory()->create([
        'dish_id' => $biryani->id,
        'type' => VariationType::Single,
        'status' => 'active',
        'price' => Money::of(650),
    ]);
    DishVariation::factory()->bulk(5)->create([
        'dish_id' => $biryani->id,
        'status' => 'active',
        'price' => Money::of(5000),
        'sort_order' => 1,
    ]);
    DishVariation::factory()->bulk(10)->create([
        'dish_id' => $biryani->id,
        'status' => 'active',
        'price' => Money::of(9000),
        'sort_order' => 2,
    ]);
    DishVariation::factory()->bulk(8)->create([
        'dish_id' => $biryani->id,
        'status' => 'inactive',
        'price' => Money::of(100),
    ]);

    $karahi = Dish::factory()->create([
        'category_id' => $chicken->id,
        'title' => 'Chicken Karahi',
        'status' => 'active',
        'sort_order' => 2,
    ]);
    DishVariation::factory()->bulk(5)->create([
        'dish_id' => $karahi->id,
        'status' => 'active',
        'price' => Money::of(7000),
    ]);

    $singleOnly = Dish::factory()->create([
        'category_id' => $rice->id,
        'title' => 'Single Only',
        'status' => 'active',
    ]);
    DishVariation::factory()->create([
        'dish_id' => $singleOnly->id,
        'type' => VariationType::Single,
        'status' => 'active',
    ]);

    Dish::factory()->create([
        'category_id' => $rice->id,
        'title' => 'Hidden Bulk Dish',
        'status' => 'inactive',
    ]);

    $this->get(route('bulk-orders'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/bulk-orders')
            ->has('categories', 2)
            ->has('dishes', 2)
            ->where('dishes_count', 2)
            ->where('dishes.0.title', 'Chicken Biryani')
            ->where('dishes.0.starting_price_formatted', 'Rs. 5,000')
            ->where('dishes.0.cartable', false)
            ->has('dishes.0.tiers', 2)
            ->where('dishes.0.tiers.0.label', '5 KG')
            ->where('filters.category', null));

    $this->get(route('bulk-orders', ['category' => 'chicken']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/bulk-orders')
            ->has('dishes', 1)
            ->where('filters.category', 'chicken')
            ->where('dishes.0.title', 'Chicken Karahi'));
});

test('bulk orders ignores unknown category filters', function () {
    $this->get(route('bulk-orders', ['category' => 'does-not-exist']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/bulk-orders')
            ->where('filters.category', null));
});
