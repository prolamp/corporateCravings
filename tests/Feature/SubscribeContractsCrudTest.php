<?php

use App\Models\User;
use App\Modules\Packages\Models\Package;
use App\Modules\Subscribers\Models\SubscribeContract;
use App\Modules\Subscribers\Models\SubscriberPackageHistory;
use App\Modules\Subscribers\Models\SubscriberSchedule;
use App\Support\Enums\UserRole;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

function actingAsContractManager(UserRole $role = UserRole::Admin): User
{
    $user = User::factory()->create(['status' => 'active']);
    $user->assignRole($role->value);

    return $user;
}

function makeSubscriberUser(): User
{
    $subscriber = User::factory()->create([
        'company_name' => 'Acme Corp',
        'status' => 'active',
    ]);
    $subscriber->assignRole(UserRole::Subscriber->value);

    return $subscriber;
}

test('guests cannot access contracts', function () {
    $this->get(route('admin.contracts.index'))->assertRedirect(route('login'));
});

test('assistant can manage contracts', function () {
    $assistant = actingAsContractManager(UserRole::Assistant);
    SubscribeContract::factory()->create();

    $this->actingAs($assistant)
        ->get(route('admin.contracts.index'))
        ->assertOk();
});

test('admin can list contracts', function () {
    $admin = actingAsContractManager();
    SubscribeContract::factory()->count(2)->create();

    $this->actingAs($admin)
        ->get(route('admin.contracts.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/contracts/index')
            ->has('contracts.data', 2)
            ->where('canManage', true));
});

test('admin can create a contract with agreed total', function () {
    $admin = actingAsContractManager();
    $subscriber = makeSubscriberUser();
    $package = Package::factory()->create([
        'title' => 'Office Lunch',
        'total_amount_per_person' => 500,
        'status' => 'active',
    ]);

    $this->actingAs($admin)
        ->post(route('admin.contracts.store'), [
            'user_id' => $subscriber->id,
            'package_id' => $package->id,
            'meal_type' => 'lunch',
            'days_of_week' => ['mon', 'wed', 'fri'],
            'persons' => 25,
            'delivery_time' => '12:30',
            'package_amount' => 500,
            'discount' => 50,
            'status' => 'active',
            'starts_on' => now()->toDateString(),
            'ends_on' => null,
            'notes' => 'Gate 2',
        ])
        ->assertRedirect();

    $contract = SubscribeContract::query()->where('user_id', $subscriber->id)->first();

    expect($contract)->not->toBeNull()
        ->and((float) $contract->package_amount)->toBe(500.0)
        ->and((float) $contract->discount)->toBe(50.0)
        ->and((float) $contract->agreed_total)->toBe(450.0)
        ->and($contract->days_of_week)->toBe(['mon', 'wed', 'fri']);

    expect(SubscriberPackageHistory::query()
        ->where('subscribe_contract_id', $contract->id)
        ->count())->toBe(1);
});

test('admin can update contract and record pricing history', function () {
    $admin = actingAsContractManager();
    $subscriber = makeSubscriberUser();
    $package = Package::factory()->create([
        'status' => 'active',
        'total_amount_per_person' => 400,
    ]);
    $contract = SubscribeContract::factory()->create([
        'user_id' => $subscriber->id,
        'package_id' => $package->id,
        'package_amount' => 400,
        'discount' => 0,
        'agreed_total' => 400,
        'persons' => 10,
    ]);
    SubscriberPackageHistory::query()->create([
        'subscribe_contract_id' => $contract->id,
        'package_id' => $package->id,
        'package_title' => $package->title,
        'package_amount' => 400,
        'discount' => 0,
        'agreed_total' => 400,
        'persons' => 10,
        'effective_from' => now()->subDay(),
        'effective_to' => null,
    ]);

    $this->actingAs($admin)
        ->put(route('admin.contracts.update', $contract), [
            'user_id' => $subscriber->id,
            'package_id' => $package->id,
            'meal_type' => 'lunch',
            'days_of_week' => ['mon', 'tue', 'wed', 'thu', 'fri'],
            'persons' => 20,
            'delivery_time' => '13:00',
            'discount' => 40,
            'status' => 'active',
            'starts_on' => now()->toDateString(),
            'notes' => null,
        ])
        ->assertRedirect();

    $fresh = $contract->fresh();

    expect((float) $fresh->package_amount)->toBe(400.0)
        ->and((float) $fresh->agreed_total)->toBe(360.0)
        ->and((int) $fresh->persons)->toBe(20);

    expect(SubscriberPackageHistory::query()
        ->where('subscribe_contract_id', $contract->id)
        ->count())->toBe(2);

    expect(SubscriberPackageHistory::query()
        ->where('subscribe_contract_id', $contract->id)
        ->whereNull('effective_to')
        ->count())->toBe(1);
});

test('admin can toggle contract status', function () {
    $admin = actingAsContractManager();
    $contract = SubscribeContract::factory()->create(['status' => 'active']);

    $this->actingAs($admin)
        ->patch(route('admin.contracts.toggle-status', $contract))
        ->assertRedirect();

    expect($contract->fresh()->status)->toBe('inactive');
});

test('admin cannot delete a contract with schedules', function () {
    $admin = actingAsContractManager();
    $contract = SubscribeContract::factory()->create();
    SubscriberSchedule::factory()->create([
        'subscribe_contract_id' => $contract->id,
    ]);

    $this->actingAs($admin)
        ->delete(route('admin.contracts.destroy', $contract))
        ->assertRedirect()
        ->assertSessionHas('error');

    expect(SubscribeContract::query()->whereKey($contract->id)->exists())->toBeTrue();
});

test('admin can delete an unused contract', function () {
    $admin = actingAsContractManager();
    $contract = SubscribeContract::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.contracts.destroy', $contract))
        ->assertRedirect(route('admin.contracts.index'));

    expect(SubscribeContract::withTrashed()->whereKey($contract->id)->whereNotNull('deleted_at')->exists())
        ->toBeTrue();
});

test('create rejects discount above package amount', function () {
    $admin = actingAsContractManager();
    $subscriber = makeSubscriberUser();
    $package = Package::factory()->create([
        'status' => 'active',
        'total_amount_per_person' => 100,
    ]);

    $this->actingAs($admin)
        ->from(route('admin.contracts.create'))
        ->post(route('admin.contracts.store'), [
            'user_id' => $subscriber->id,
            'package_id' => $package->id,
            'meal_type' => 'lunch',
            'days_of_week' => ['mon'],
            'persons' => 5,
            'discount' => 150,
            'status' => 'active',
        ])
        ->assertRedirect(route('admin.contracts.create'))
        ->assertSessionHas('error');
});

test('contract package amount always follows package catalog rate', function () {
    $admin = actingAsContractManager();
    $subscriber = makeSubscriberUser();
    $package = Package::factory()->create([
        'status' => 'active',
        'total_amount_per_person' => 440,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.contracts.store'), [
            'user_id' => $subscriber->id,
            'package_id' => $package->id,
            'meal_type' => 'lunch',
            'days_of_week' => ['mon', 'tue', 'wed', 'thu', 'fri'],
            'persons' => 10,
            'package_amount' => 999,
            'discount' => 40,
            'status' => 'active',
        ])
        ->assertRedirect();

    $contract = SubscribeContract::query()->where('user_id', $subscriber->id)->first();

    expect($contract)->not->toBeNull()
        ->and((float) $contract->package_amount)->toBe(440.0)
        ->and((float) $contract->agreed_total)->toBe(400.0);
});
