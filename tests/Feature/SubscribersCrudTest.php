<?php

use App\Models\User;
use App\Modules\Packages\Models\Package;
use App\Modules\Subscribers\Models\SubscribeContract;
use App\Support\Enums\UserRole;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

function actingAsSubscriberManager(UserRole $role = UserRole::Admin): User
{
    $user = User::factory()->create(['status' => 'active']);
    $user->assignRole($role->value);

    return $user;
}

test('guests cannot access subscribers', function () {
    $this->get(route('admin.subscribers.index'))->assertRedirect(route('login'));
});

test('assistant can view subscribers but cannot create', function () {
    $assistant = actingAsSubscriberManager(UserRole::Assistant);

    $this->actingAs($assistant)
        ->get(route('admin.subscribers.index'))
        ->assertOk();

    $this->actingAs($assistant)
        ->get(route('admin.subscribers.create'))
        ->assertForbidden();
});

test('admin can list subscribers only', function () {
    $admin = actingAsSubscriberManager();
    User::factory()->create(['status' => 'active'])->assignRole(UserRole::Subscriber->value);
    User::factory()->create(['status' => 'active'])->assignRole(UserRole::Customer->value);

    $this->actingAs($admin)
        ->get(route('admin.subscribers.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/subscribers/index')
            ->has('subscribers.data', 1)
            ->where('canCreate', true));
});

test('admin can create a subscriber company', function () {
    $admin = actingAsSubscriberManager();

    $this->actingAs($admin)
        ->post(route('admin.subscribers.store'), [
            'name' => 'Ali Khan',
            'company_name' => 'Khan Foods',
            'phone' => '03009998877',
            'email' => 'khan@example.com',
            'address' => 'I-8 Islamabad',
            'status' => 'active',
        ])
        ->assertRedirect();

    $subscriber = User::query()->where('phone', '03009998877')->first();

    expect($subscriber)->not->toBeNull()
        ->and($subscriber->company_name)->toBe('Khan Foods')
        ->and($subscriber->hasRole(UserRole::Subscriber->value))->toBeTrue();
});

test('admin can update and toggle a subscriber', function () {
    $admin = actingAsSubscriberManager();
    $subscriber = User::factory()->create([
        'name' => 'Old Contact',
        'company_name' => 'Old Co',
        'phone' => '03001112233',
        'status' => 'active',
    ]);
    $subscriber->assignRole(UserRole::Subscriber->value);

    $this->actingAs($admin)
        ->put(route('admin.subscribers.update', $subscriber), [
            'name' => 'New Contact',
            'company_name' => 'New Co',
            'phone' => '03001112233',
            'email' => null,
            'address' => 'G-9',
            'status' => 'active',
        ])
        ->assertRedirect();

    expect($subscriber->fresh()->company_name)->toBe('New Co');

    $this->actingAs($admin)
        ->patch(route('admin.subscribers.toggle-status', $subscriber))
        ->assertRedirect();

    expect($subscriber->fresh()->status)->toBe('inactive');
});

test('admin cannot delete a subscriber with a contract', function () {
    $admin = actingAsSubscriberManager();
    $subscriber = User::factory()->create(['status' => 'active']);
    $subscriber->assignRole(UserRole::Subscriber->value);
    $package = Package::factory()->create();
    SubscribeContract::factory()->create([
        'user_id' => $subscriber->id,
        'package_id' => $package->id,
    ]);

    $this->actingAs($admin)
        ->delete(route('admin.subscribers.destroy', $subscriber))
        ->assertRedirect()
        ->assertSessionHas('error');

    expect(User::query()->whereKey($subscriber->id)->exists())->toBeTrue();
});

test('show includes read-only contract summary', function () {
    $admin = actingAsSubscriberManager();
    $subscriber = User::factory()->create([
        'company_name' => 'Factory Foods Co',
        'status' => 'active',
    ]);
    $subscriber->assignRole(UserRole::Subscriber->value);
    $package = Package::factory()->create(['title' => 'Office Lunch Combo']);
    SubscribeContract::factory()->create([
        'user_id' => $subscriber->id,
        'package_id' => $package->id,
        'agreed_total' => 450,
        'status' => 'active',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.subscribers.show', $subscriber))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/subscribers/show')
            ->where('subscriber.contract.package_title', 'Office Lunch Combo')
            ->where('subscriber.contract.agreed_total', 450)
            ->has('dailyBills', 0));
});

test('show includes delivered daily bills listing', function () {
    $admin = actingAsSubscriberManager();
    $subscriber = User::factory()->create([
        'company_name' => 'Billable Co',
        'status' => 'active',
    ]);
    $subscriber->assignRole(UserRole::Subscriber->value);
    $package = Package::factory()->create();
    $contract = SubscribeContract::factory()->create([
        'user_id' => $subscriber->id,
        'package_id' => $package->id,
        'meal_type' => 'lunch',
        'agreed_total' => 390,
    ]);
    $schedule = \App\Modules\Subscribers\Models\SubscriberSchedule::factory()->create([
        'subscribe_contract_id' => $contract->id,
        'package_id' => $package->id,
        'date' => '2026-07-28',
        'dish_title' => 'Chicken Biryani',
        'persons' => 50,
        'unit_price' => 390,
        'total' => 19500,
        'status' => 'delivered',
    ]);
    \App\Modules\Orders\Models\Order::factory()->create([
        'user_id' => $subscriber->id,
        'type' => \App\Support\Enums\OrderType::Subscription,
        'status' => 'delivered',
        'subscribe_contract_id' => $contract->id,
        'subscriber_schedule_id' => $schedule->id,
        'quoted_amount' => 19500,
        'subtotal' => 19500,
        'placed_at' => '2026-07-28 13:00:00',
    ]);
    // Pending order must not appear.
    \App\Modules\Orders\Models\Order::factory()->create([
        'user_id' => $subscriber->id,
        'type' => \App\Support\Enums\OrderType::Subscription,
        'status' => 'pending',
        'subscribe_contract_id' => $contract->id,
        'quoted_amount' => 1000,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.subscribers.show', $subscriber))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/subscribers/show')
            ->has('dailyBills', 1)
            ->where('dailyBills.0.meal', 'Chicken Biryani')
            ->where('dailyBills.0.persons', 50)
            ->where('dailyBills.0.day', 'Tue')
            ->where('dailyBills.0.total', 19500));
});

