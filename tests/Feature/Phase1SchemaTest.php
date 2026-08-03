<?php

use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Dish;
use App\Modules\Catalog\Models\DishVariation;
use App\Modules\Delivery\Models\DeliveryZone;
use App\Modules\Packages\Models\Package;
use App\Modules\Subscribers\Models\SubscribeContract;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Storage;

test('phase 1 schema has catalog package and subscribe contract seed data', function () {
    $this->seed(DatabaseSeeder::class);

    expect(Category::query()->count())->toBeGreaterThanOrEqual(9)
        ->and(Dish::query()->count())->toBeGreaterThanOrEqual(20)
        ->and(DishVariation::query()->single()->count())->toBeGreaterThan(0)
        ->and(DishVariation::query()->bulk()->count())->toBeGreaterThan(0)
        ->and(DeliveryZone::query()->count())->toBe(12)
        ->and(Package::query()->where('slug', 'office-lunch-combo')->exists())->toBeTrue()
        ->and(SubscribeContract::query()->count())->toBeGreaterThanOrEqual(1);

    $dish = Dish::query()->whereNotNull('image')->first();
    expect($dish)->not->toBeNull()
        ->and(Storage::disk('public')->exists($dish->image))->toBeTrue();
})->group('phase1');
