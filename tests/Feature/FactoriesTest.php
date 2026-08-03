<?php

use App\Models\Page;
use App\Models\Setting;
use App\Models\User;
use App\Modules\Addons\Models\Addon;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Dish;
use App\Modules\Catalog\Models\DishVariation;
use App\Modules\Delivery\Models\DeliveryZone;
use App\Modules\Expenses\Models\Expense;
use App\Modules\Expenses\Models\ExpenseCategory;
use App\Modules\Orders\Models\Order;
use App\Modules\Packages\Models\Package;
use App\Modules\Subscribers\Models\SubscribeContract;
use App\Modules\Subscribers\Models\SubscriberSchedule;
use App\Support\DishCostCalculator;
use App\Support\Enums\UserRole;
use App\Support\Enums\VariationType;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can create every model through its factory', function (string $model) {
    $instance = $model::factory()->create();

    expect($instance->exists)->toBeTrue();
})->with([
    Category::class,
    Dish::class,
    DishVariation::class,
    Addon::class,
    Package::class,
    DeliveryZone::class,
    SubscribeContract::class,
    SubscriberSchedule::class,
    Order::class,
    ExpenseCategory::class,
    Expense::class,
    Page::class,
    Setting::class,
]);

it('creates users with the requested role', function () {
    expect(User::factory()->subscriber()->create()->isSubscriber())->toBeTrue()
        ->and(User::factory()->superAdmin()->create()->isSuperAdmin())->toBeTrue()
        ->and(User::factory()->manager()->create()->primaryRole())->toBe(UserRole::Manager);
});

it('keeps package pricing consistent', function () {
    $package = Package::factory()->create();

    expect((float) $package->total_amount_per_person)
        ->toBe((float) $package->base_amount_per_person - (float) $package->discount_amount);
});

it('links a subscriber contract to a subscriber user', function () {
    $contract = SubscribeContract::factory()->create();

    expect($contract->user->isSubscriber())->toBeTrue()
        ->and((float) $contract->agreed_total)
        ->toBe((float) $contract->package_amount - (float) $contract->discount);
});

it('builds bulk dish variations that are not cartable', function () {
    $variation = DishVariation::factory()->bulk(8)->create();

    expect($variation->type)->toBe(VariationType::Bulk)
        ->and($variation->isCartable())->toBeFalse()
        ->and($variation->unit)->toBe('kg');
});

it('only allows single servings into the cart', function () {
    $single = DishVariation::factory()->create();

    expect($single->type)->toBe(VariationType::Single)
        ->and($single->isCartable())->toBeTrue()
        ->and($single->fresh()->update(['status' => 'inactive']))->toBeTrue()
        ->and($single->fresh()->isCartable())->toBeFalse();
});

it('estimates dish cost from grocery expense unit prices', function () {
    // Chicken @ 800/kg × 2kg + Rice @ 300/kg × 5kg + Oil @ 500/l × 0.5l = 1600+1500+250 = 3350
    // Yield 50 servings → 67 per portion
    $result = DishCostCalculator::estimate([
        ['unit_price' => 800, 'quantity' => 2],
        ['unit_price' => 300, 'quantity' => 5],
        ['unit_price' => 500, 'quantity' => 0.5],
    ], 50);

    expect($result['total_cost'])->toBe(3350.0)
        ->and($result['cost_per_portion'])->toBe(67.0)
        ->and($result['line_costs'])->toBe([1600.0, 1500.0, 250.0]);
});
