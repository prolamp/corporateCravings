<?php

use App\Modules\Catalog\Models\Dish;
use App\Modules\Packages\Models\Package;
use App\Modules\Packages\Models\PackageItem;
use App\Support\Money;
use Database\Seeders\RolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('packages listing shows active packages with public pricing', function () {
    $active = Package::factory()->create([
        'title' => 'Boardroom Lunch',
        'slug' => 'boardroom-lunch',
        'status' => 'active',
        'total_amount_per_person' => Money::of(850),
        'base_amount_per_person' => Money::of(950),
        'sort_order' => 1,
    ]);

    Package::factory()->create([
        'title' => 'Hidden Package',
        'slug' => 'hidden-package',
        'status' => 'inactive',
        'total_amount_per_person' => Money::of(500),
    ]);

    Package::factory()->create([
        'title' => 'Executive Buffet',
        'slug' => 'executive-buffet',
        'status' => 'active',
        'total_amount_per_person' => Money::of(1200),
        'sort_order' => 2,
    ]);

    $dish = Dish::factory()->create(['status' => 'active']);
    PackageItem::query()->create([
        'package_id' => $active->id,
        'dish_id' => $dish->id,
        'dish_variation_id' => null,
        'unit_price' => Money::of(850),
        'sort_order' => 1,
    ]);

    $this->get(route('packages'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/packages')
            ->has('packages', 2)
            ->where('packages_count', 2)
            ->where('package_from_formatted', 'Rs. 850')
            ->where('packages.0.title', 'Boardroom Lunch')
            ->where('packages.0.price_per_person_formatted', 'Rs. 850')
            ->where('packages.0.items_count', 1)
            ->missing('packages.0.agreed_total'));
});

test('package detail shows included dishes and hides inactive packages', function () {
    $package = Package::factory()->create([
        'title' => 'Seminar Package',
        'slug' => 'seminar-package',
        'status' => 'active',
        'short_description' => 'Full-course office lunch.',
        'base_amount_per_person' => Money::of(1000),
        'total_amount_per_person' => Money::of(900),
    ]);

    $activeDish = Dish::factory()->create([
        'title' => 'Chicken Biryani',
        'status' => 'active',
    ]);
    $inactiveDish = Dish::factory()->create([
        'title' => 'Hidden Dish',
        'status' => 'inactive',
    ]);

    PackageItem::query()->create([
        'package_id' => $package->id,
        'dish_id' => $activeDish->id,
        'unit_price' => Money::of(500),
        'sort_order' => 1,
    ]);
    PackageItem::query()->create([
        'package_id' => $package->id,
        'dish_id' => $inactiveDish->id,
        'unit_price' => Money::of(400),
        'sort_order' => 2,
    ]);

    $this->get(route('packages.show', 'seminar-package'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/packages/show')
            ->where('package.title', 'Seminar Package')
            ->where('package.price_per_person_formatted', 'Rs. 900')
            ->where('package.has_discount', true)
            ->has('package.items', 1)
            ->where('package.items.0.dish_title', 'Chicken Biryani')
            ->missing('package.agreed_total')
            ->missing('package.discount_amount'));

    Package::factory()->create([
        'title' => 'Inactive',
        'slug' => 'inactive-pack',
        'status' => 'inactive',
    ]);

    $this->get(route('packages.show', 'inactive-pack'))->assertNotFound();
    $this->get(route('packages.show', 'missing-pack'))->assertNotFound();
});
