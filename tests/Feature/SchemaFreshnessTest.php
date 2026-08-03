<?php

use App\Modules\Orders\Models\Order;
use Illuminate\Support\Facades\Schema;

/**
 * Guards the fresh schema — these legacy/old-flow tables must never exist.
 */
it('does not create legacy recipe or raw-material tables', function () {
    $forbidden = [
        'raw_materials',
        'recipe_ingredients',
        'dish_recipes',
        'dish_cost_estimates',
        'products',
        'product_variations',
        'subscribers',
    ];

    foreach ($forbidden as $table) {
        expect(Schema::hasTable($table))->toBeFalse("legacy table `{$table}` must not exist");
    }
});

it('uses the fresh catalog and finance columns', function () {
    expect(Schema::hasColumn('dishes', 'images'))->toBeFalse()
        ->and(Schema::hasColumn('dish_variations', 'is_single'))->toBeFalse()
        ->and(Schema::hasColumn('dish_variations', 'is_cartable'))->toBeFalse()
        ->and(Schema::hasColumn('dish_variations', 'is_bulk'))->toBeFalse()
        ->and(Schema::hasColumn('dish_variations', 'on_listing'))->toBeFalse()
        ->and(Schema::hasColumn('dish_variations', 'type'))->toBeTrue()
        ->and(Schema::hasColumn('expenses', 'raw_material_id'))->toBeFalse()
        ->and(Schema::hasColumn('expenses', 'unit'))->toBeTrue()
        ->and(Schema::hasColumn('users', 'phone'))->toBeTrue()
        ->and(Schema::hasColumn('users', 'company_name'))->toBeTrue()
        ->and(Schema::hasColumn('users', 'two_factor_secret'))->toBeTrue();
});

it('has the expected core business tables', function () {
    $required = [
        'users',
        'categories',
        'dishes',
        'dish_variations',
        'addons',
        'dish_addon',
        'packages',
        'package_items',
        'delivery_zones',
        'subscribe_contracts',
        'subscriber_package_history',
        'subscriber_schedules',
        'orders',
        'order_items',
        'order_item_addons',
        'order_status_histories',
        'expense_categories',
        'expenses',
        'settings',
        'pages',
    ];

    foreach ($required as $table) {
        expect(Schema::hasTable($table))->toBeTrue("missing required table `{$table}`");
    }
});

it('indexes hot lookup columns for performance', function () {
    expect(Schema::hasIndex('orders', ['customer_phone', 'status']))->toBeTrue()
        ->and(Schema::hasIndex('orders', ['user_id', 'status']))->toBeTrue()
        ->and(Schema::hasIndex('categories', ['status', 'sort_order']))->toBeTrue()
        ->and(Schema::hasIndex('packages', ['status', 'sort_order']))->toBeTrue()
        ->and(Schema::hasIndex('addons', ['status', 'is_global', 'sort_order']))->toBeTrue()
        ->and(Schema::hasIndex('delivery_zones', ['is_active', 'sort_order']))->toBeTrue()
        ->and(Schema::hasIndex('expenses', ['expense_category_id', 'title']))->toBeTrue();
});

it('keeps amount_paid out of mass assignment', function () {
    expect((new Order)->isFillable('amount_paid'))->toBeFalse()
        ->and((new Order)->isFillable('quoted_amount'))->toBeTrue();
});

it('wires core eloquent relations', function () {
    $user = new \App\Models\User;
    $dish = new \App\Modules\Catalog\Models\Dish;
    $package = new \App\Modules\Packages\Models\Package;
    $contract = new \App\Modules\Subscribers\Models\SubscribeContract;
    $zone = new \App\Modules\Delivery\Models\DeliveryZone;

    expect($user->orders())->toBeInstanceOf(Illuminate\Database\Eloquent\Relations\HasMany::class)
        ->and($user->subscribeContracts())->toBeInstanceOf(Illuminate\Database\Eloquent\Relations\HasMany::class)
        ->and($dish->singleVariations())->toBeInstanceOf(Illuminate\Database\Eloquent\Relations\HasMany::class)
        ->and($dish->bulkVariations())->toBeInstanceOf(Illuminate\Database\Eloquent\Relations\HasMany::class)
        ->and($package->dishes())->toBeInstanceOf(Illuminate\Database\Eloquent\Relations\BelongsToMany::class)
        ->and($package->contracts())->toBeInstanceOf(Illuminate\Database\Eloquent\Relations\HasMany::class)
        ->and($contract->orders())->toBeInstanceOf(Illuminate\Database\Eloquent\Relations\HasMany::class)
        ->and($zone->orders())->toBeInstanceOf(Illuminate\Database\Eloquent\Relations\HasMany::class);
});
