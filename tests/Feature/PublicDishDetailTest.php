<?php

use App\Modules\Addons\Models\Addon;
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

test('dish detail shows single cartable options bulk whatsapp tiers and addons', function () {
    $category = Category::factory()->create([
        'name' => 'Rice',
        'slug' => 'rice',
        'status' => 'active',
    ]);

    $dish = Dish::factory()->create([
        'category_id' => $category->id,
        'title' => 'Chicken Biryani',
        'slug' => 'chicken-biryani',
        'status' => 'active',
        'short_description' => 'Aromatic basmati rice.',
    ]);

    DishVariation::factory()->create([
        'dish_id' => $dish->id,
        'label' => '1 Serving',
        'type' => VariationType::Single,
        'status' => 'active',
        'price' => Money::of(650),
        'sort_order' => 1,
    ]);

    DishVariation::factory()->bulk(5)->create([
        'dish_id' => $dish->id,
        'label' => '5 KG',
        'status' => 'active',
        'price' => Money::of(5000),
        'sort_order' => 2,
    ]);

    DishVariation::factory()->create([
        'dish_id' => $dish->id,
        'label' => 'Hidden single',
        'type' => VariationType::Single,
        'status' => 'inactive',
        'price' => Money::of(100),
    ]);

    Addon::factory()->create([
        'title' => 'Raita',
        'price' => Money::of(80),
        'is_global' => true,
        'status' => 'active',
    ]);

    $linked = Addon::factory()->create([
        'title' => 'Extra Chicken',
        'price' => Money::of(200),
        'is_global' => false,
        'status' => 'active',
    ]);
    $dish->addons()->attach($linked->id);

    Addon::factory()->create([
        'title' => 'Inactive Drink',
        'is_global' => true,
        'status' => 'inactive',
    ]);

    $this->get(route('menu.show', 'chicken-biryani'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/menu/show')
            ->where('dish.title', 'Chicken Biryani')
            ->where('dish.category_slug', 'rice')
            ->has('single_variations', 1)
            ->where('single_variations.0.cartable', true)
            ->where('single_variations.0.price_formatted', 'Rs. 650')
            ->has('bulk_variations', 1)
            ->where('bulk_variations.0.cartable', false)
            ->where('bulk_variations.0.label', '5 KG')
            ->has('addons', 2)
            ->where('addons', fn ($addons) => collect($addons)->pluck('title')->sort()->values()->all() === ['Extra Chicken', 'Raita']));
});

test('inactive or missing dish detail returns 404', function () {
    Dish::factory()->create([
        'title' => 'Hidden',
        'slug' => 'hidden-dish',
        'status' => 'inactive',
    ]);

    $this->get(route('menu.show', 'hidden-dish'))->assertNotFound();
    $this->get(route('menu.show', 'does-not-exist'))->assertNotFound();
});
