<?php

use App\Models\User;
use App\Modules\Catalog\Models\Dish;
use App\Modules\Orders\Models\Order;
use App\Modules\Packages\Models\Package;
use App\Modules\Packages\Models\PackageItem;
use App\Modules\Subscribers\Models\SubscribeContract;
use App\Modules\Subscribers\Models\SubscriberSchedule;
use App\Support\Enums\UserRole;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

function actingAsScheduleManager(UserRole $role = UserRole::Admin): User
{
    $user = User::factory()->create(['status' => 'active']);
    $user->assignRole($role->value);

    return $user;
}

function makeContractWithPackage(): array
{
    $subscriber = User::factory()->create([
        'company_name' => 'Schedule Co',
        'status' => 'active',
    ]);
    $subscriber->assignRole(UserRole::Subscriber->value);

    $package = Package::factory()->create(['status' => 'active', 'title' => 'Office Lunch']);
    $dishA = Dish::factory()->create(['title' => 'Biryani', 'status' => 'active']);
    $dishB = Dish::factory()->create(['title' => 'Karahi', 'status' => 'active']);
    PackageItem::query()->create([
        'package_id' => $package->id,
        'dish_id' => $dishA->id,
        'unit_price' => 400,
        'sort_order' => 1,
    ]);
    PackageItem::query()->create([
        'package_id' => $package->id,
        'dish_id' => $dishB->id,
        'unit_price' => 400,
        'sort_order' => 2,
    ]);

    $contract = SubscribeContract::factory()->create([
        'user_id' => $subscriber->id,
        'package_id' => $package->id,
        'meal_type' => 'lunch',
        'days_of_week' => ['mon', 'tue', 'wed', 'thu', 'fri'],
        'persons' => 20,
        'package_amount' => 400,
        'discount' => 50,
        'agreed_total' => 350,
        'status' => 'active',
        'starts_on' => '2026-07-01',
        'ends_on' => null,
    ]);

    return compact('subscriber', 'package', 'contract', 'dishA', 'dishB');
}

test('guests cannot access schedules', function () {
    $this->get(route('admin.schedules.index'))->assertRedirect(route('login'));
});

test('assistant can view schedules', function () {
    $assistant = actingAsScheduleManager(UserRole::Assistant);

    $this->actingAs($assistant)
        ->get(route('admin.schedules.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('admin/schedules/index'));
});

test('admin can generate a month of schedules', function () {
    $admin = actingAsScheduleManager();
    ['contract' => $contract] = makeContractWithPackage();

    $this->actingAs($admin)
        ->post(route('admin.schedules.generate'), [
            'contract_id' => $contract->id,
            'year' => 2026,
            'month' => 7,
        ])
        ->assertRedirect();

    $count = SubscriberSchedule::query()
        ->where('subscribe_contract_id', $contract->id)
        ->whereMonth('date', 7)
        ->whereYear('date', 2026)
        ->count();

    // July 2026 has 23 weekdays Mon–Fri
    expect($count)->toBe(23);

    $this->actingAs($admin)
        ->post(route('admin.schedules.generate'), [
            'contract_id' => $contract->id,
            'year' => 2026,
            'month' => 7,
        ])
        ->assertRedirect();

    expect(SubscriberSchedule::query()
        ->where('subscribe_contract_id', $contract->id)
        ->whereMonth('date', 7)
        ->whereYear('date', 2026)
        ->count())->toBe(23);
});

test('admin can shuffle and update a schedule day', function () {
    $admin = actingAsScheduleManager();
    ['contract' => $contract, 'dishB' => $dishB] = makeContractWithPackage();

    $this->actingAs($admin)
        ->post(route('admin.schedules.generate'), [
            'contract_id' => $contract->id,
            'year' => 2026,
            'month' => 7,
        ]);

    $day = SubscriberSchedule::query()
        ->where('subscribe_contract_id', $contract->id)
        ->orderBy('date')
        ->first();

    $this->actingAs($admin)
        ->post(route('admin.schedules.shuffle'), [
            'contract_id' => $contract->id,
            'year' => 2026,
            'month' => 7,
        ])
        ->assertRedirect();

    $this->actingAs($admin)
        ->put(route('admin.schedules.update', $day), [
            'dish_id' => $dishB->id,
            'persons' => 30,
            'status' => 'confirmed',
            'notes' => 'Gate B',
        ])
        ->assertRedirect();

    $fresh = $day->fresh();
    expect($fresh->dish_id)->toBe($dishB->id)
        ->and((int) $fresh->persons)->toBe(30)
        ->and($fresh->status)->toBe('confirmed')
        ->and((float) $fresh->total)->toBe(10500.0);
});

test('deliver creates subscription order for daily bills', function () {
    $admin = actingAsScheduleManager();
    ['subscriber' => $subscriber, 'contract' => $contract] = makeContractWithPackage();

    $schedule = SubscriberSchedule::factory()->create([
        'subscribe_contract_id' => $contract->id,
        'package_id' => $contract->package_id,
        'date' => '2026-07-28',
        'dish_title' => 'Biryani',
        'persons' => 20,
        'unit_price' => 350,
        'total' => 7000,
        'status' => 'scheduled',
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.schedules.deliver', $schedule))
        ->assertRedirect();

    $fresh = $schedule->fresh();
    expect($fresh->status)->toBe('delivered');

    $order = Order::query()->where('subscriber_schedule_id', $schedule->id)->first();
    expect($order)->not->toBeNull()
        ->and($order->status)->toBe('delivered')
        ->and((float) $order->quoted_amount)->toBe(7000.0);

    $this->actingAs($admin)
        ->get(route('admin.subscribers.show', $subscriber))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('dailyBills', 1)
            ->where('dailyBills.0.total', 7000));
});

test('cannot delete a delivered schedule', function () {
    $admin = actingAsScheduleManager();
    ['contract' => $contract] = makeContractWithPackage();
    $schedule = SubscriberSchedule::factory()->delivered()->create([
        'subscribe_contract_id' => $contract->id,
        'date' => '2026-07-29',
    ]);

    $this->actingAs($admin)
        ->delete(route('admin.schedules.destroy', $schedule))
        ->assertRedirect()
        ->assertSessionHas('error');

    expect(SubscriberSchedule::query()->whereKey($schedule->id)->exists())->toBeTrue();
});
