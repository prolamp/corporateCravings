<?php

use App\Modules\Catalog\Models\Dish;
use App\Modules\Catalog\Models\DishVariation;
use App\Modules\Packages\Models\Package;
use App\Support\Enums\VariationType;
use App\Support\Money;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('home page is public and uses public layout data', function () {
    $dish = Dish::factory()->create([
        'title' => 'Chicken Biryani',
        'status' => 'active',
        'sort_order' => 1,
        'short_description' => 'Saffron rice and tender chicken.',
    ]);

    DishVariation::factory()->create([
        'dish_id' => $dish->id,
        'type' => VariationType::Single,
        'status' => 'active',
        'price' => Money::of(650),
        'sort_order' => 1,
    ]);

    Package::factory()->create([
        'status' => 'active',
        'total_amount_per_person' => Money::of(850),
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('public/home')
            ->has('featured_dishes', 1)
            ->where('featured_dishes.0.title', 'Chicken Biryani')
            ->where('featured_dishes.0.starting_price', 650)
            ->where('package_from_formatted', 'Rs. 850')
            ->where('hero_image_url', fn ($url) => str_contains((string) $url, '/images/home/hero.'))
            ->where('trust_image_url', fn ($url) => str_contains((string) $url, '/images/home/trust.'))
            ->has('site.brand.name')
            ->has('site.whatsapp_url'));
});

test('inactive dishes are not featured on home', function () {
    Dish::factory()->create(['status' => 'inactive', 'sort_order' => 1]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('public/home')
            ->has('featured_dishes', 0));
});
