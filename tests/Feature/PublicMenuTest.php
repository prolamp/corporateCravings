<?php

use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Dish;
use App\Modules\Catalog\Models\DishVariation;
use App\Support\Enums\VariationType;
use App\Support\Money;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('menu page lists active single-serving dishes with category chips', function () {
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
    ]);

    $karahi = Dish::factory()->create([
        'category_id' => $chicken->id,
        'title' => 'Chicken Karahi',
        'status' => 'active',
        'sort_order' => 2,
    ]);
    DishVariation::factory()->create([
        'dish_id' => $karahi->id,
        'type' => VariationType::Single,
        'status' => 'active',
        'price' => Money::of(900),
    ]);

    $bulkOnly = Dish::factory()->create([
        'category_id' => $rice->id,
        'title' => 'Bulk Only Tray',
        'status' => 'active',
    ]);
    DishVariation::factory()->bulk(10)->create([
        'dish_id' => $bulkOnly->id,
        'status' => 'active',
    ]);

    Dish::factory()->create([
        'category_id' => $rice->id,
        'title' => 'Hidden Dish',
        'status' => 'inactive',
    ]);

    $this->get(route('menu'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('public/menu')
            ->has('categories', 2)
            ->has('dishes', 2)
            ->where('dishes_count', 2)
            ->where('dishes.0.starting_price_formatted', 'Rs. 650')
            ->where('filters.category', null));

    $this->get(route('menu', ['category' => 'chicken']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('public/menu')
            ->has('dishes', 1)
            ->where('filters.category', 'chicken')
            ->where('dishes.0.title', 'Chicken Karahi'));
});

test('menu ignores unknown category filters', function () {
    $this->get(route('menu', ['category' => 'does-not-exist']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('public/menu')
            ->where('filters.category', null));
});
