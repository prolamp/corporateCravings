<?php

use App\Models\User;
use App\Modules\Catalog\Models\Dish;
use App\Modules\Packages\Models\Package;
use App\Modules\Packages\Models\PackageItem;
use App\Modules\Subscribers\Models\SubscribeContract;
use App\Modules\Subscribers\Models\SubscriberSchedule;
use App\Support\Enums\UserRole;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Artisan;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

function makeRolloverContract(): array
{
    $subscriber = User::factory()->create([
        'company_name' => 'Rollover Co',
        'status' => 'active',
    ]);
    $subscriber->assignRole(UserRole::Subscriber->value);

    $package = Package::factory()->create(['status' => 'active']);
    $dishA = Dish::factory()->create(['title' => 'Biryani Plate', 'status' => 'active']);
    $dishB = Dish::factory()->create(['title' => 'Karahi Plate', 'status' => 'active']);
    PackageItem::query()->create([
        'package_id' => $package->id,
        'dish_id' => $dishA->id,
        'unit_price' => 350,
        'sort_order' => 1,
    ]);
    PackageItem::query()->create([
        'package_id' => $package->id,
        'dish_id' => $dishB->id,
        'unit_price' => 350,
        'sort_order' => 2,
    ]);

    $contract = SubscribeContract::factory()->create([
        'user_id' => $subscriber->id,
        'package_id' => $package->id,
        'days_of_week' => ['mon', 'tue', 'wed', 'thu', 'fri'],
        'persons' => 40,
        'package_amount' => 400,
        'discount' => 50,
        'agreed_total' => 350,
        'status' => 'active',
        'starts_on' => '2026-06-01',
        'ends_on' => null,
    ]);

    return compact('subscriber', 'package', 'contract', 'dishA', 'dishB');
}

test('rollover copies previous month dish and persons pattern', function () {
    ['contract' => $contract, 'dishA' => $dishA, 'dishB' => $dishB] = makeRolloverContract();

    // Seed a short June pattern with varying persons.
    SubscriberSchedule::factory()->create([
        'subscribe_contract_id' => $contract->id,
        'package_id' => $contract->package_id,
        'dish_id' => $dishA->id,
        'dish_title' => $dishA->title,
        'date' => '2026-06-02', // Mon
        'persons' => 45,
        'unit_price' => 350,
        'total' => 15750,
        'status' => 'delivered',
    ]);
    SubscriberSchedule::factory()->create([
        'subscribe_contract_id' => $contract->id,
        'package_id' => $contract->package_id,
        'dish_id' => $dishB->id,
        'dish_title' => $dishB->title,
        'date' => '2026-06-03', // Tue
        'persons' => 38,
        'unit_price' => 350,
        'total' => 13300,
        'status' => 'scheduled',
    ]);

    $exit = Artisan::call('schedules:rollover-month', [
        '--year' => 2026,
        '--month' => 7,
        '--contract' => $contract->id,
    ]);

    expect($exit)->toBe(0);

    $july = SubscriberSchedule::query()
        ->where('subscribe_contract_id', $contract->id)
        ->whereMonth('date', 7)
        ->whereYear('date', 2026)
        ->orderBy('date')
        ->get();

    expect($july->count())->toBe(23)
        ->and($july->first()->dish_id)->toBe($dishA->id)
        ->and((int) $july->first()->persons)->toBe(45)
        ->and($july->skip(1)->first()->dish_id)->toBe($dishB->id)
        ->and((int) $july->skip(1)->first()->persons)->toBe(38)
        ->and((float) $july->first()->unit_price)->toBe(350.0);
});

test('rollover is idempotent and does not duplicate days', function () {
    ['contract' => $contract] = makeRolloverContract();

    Artisan::call('schedules:rollover-month', [
        '--year' => 2026,
        '--month' => 7,
        '--contract' => $contract->id,
    ]);

    $firstCount = SubscriberSchedule::query()
        ->where('subscribe_contract_id', $contract->id)
        ->whereMonth('date', 7)
        ->whereYear('date', 2026)
        ->count();

    $exit = Artisan::call('schedules:rollover-month', [
        '--year' => 2026,
        '--month' => 7,
        '--contract' => $contract->id,
    ]);

    expect($exit)->toBe(0)
        ->and(SubscriberSchedule::query()
            ->where('subscribe_contract_id', $contract->id)
            ->whereMonth('date', 7)
            ->whereYear('date', 2026)
            ->count())->toBe($firstCount);
});

test('dry-run writes nothing', function () {
    ['contract' => $contract] = makeRolloverContract();

    Artisan::call('schedules:rollover-month', [
        '--year' => 2026,
        '--month' => 8,
        '--contract' => $contract->id,
        '--dry-run' => true,
    ]);

    expect(SubscriberSchedule::query()
        ->where('subscribe_contract_id', $contract->id)
        ->whereMonth('date', 8)
        ->count())->toBe(0);
});
