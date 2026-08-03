<?php

use App\Models\User;
use App\Modules\Subscribers\Models\SubscribeContract;
use App\Modules\Subscribers\Models\SubscriberSchedule;
use App\Support\Enums\UserRole;
use Database\Seeders\RolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

function subscriberUser(): User
{
    $user = User::factory()->create([
        'email' => 'subscriber@example.com',
        'company_name' => 'Acme Corp',
        'password' => 'password',
    ]);
    $user->assignRole(UserRole::Subscriber->value);

    return $user;
}

test('guests cannot open subscriber dashboard', function () {
    $this->get(route('subscriber.dashboard'))
        ->assertRedirect(route('login'));
});

test('subscriber dashboard shows stats upcoming and contract', function () {
    $user = subscriberUser();
    $contract = SubscribeContract::factory()->forUser($user)->create([
        'agreed_total' => 450,
        'persons' => 20,
        'status' => 'active',
    ]);

    SubscriberSchedule::factory()->delivered()->create([
        'subscribe_contract_id' => $contract->id,
        'date' => now()->subDay()->toDateString(),
        'persons' => 20,
        'unit_price' => 450,
        'total' => 9000,
        'dish_title' => 'Chicken Biryani',
    ]);

    SubscriberSchedule::factory()->create([
        'subscribe_contract_id' => $contract->id,
        'date' => now()->addDay()->toDateString(),
        'status' => 'scheduled',
        'persons' => 20,
        'unit_price' => 450,
        'total' => 9000,
        'dish_title' => 'Karahi',
    ]);

    $this->actingAs($user)
        ->get(route('subscriber.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('subscriber/dashboard')
            ->where('stats.delivered_orders', 1)
            ->where('stats.total_bill_formatted', 'Rs. 9,000')
            ->where('stats.persons_served', 20)
            ->where('contract.agreed_total_formatted', 'Rs. 450')
            ->has('upcoming', 1)
            ->where('upcoming.0.dish_title', 'Karahi'));
});

test('customer cannot open subscriber portal', function () {
    $customer = User::factory()->create();
    $customer->assignRole(UserRole::Customer->value);

    $this->actingAs($customer)
        ->get(route('subscriber.dashboard'))
        ->assertForbidden();
});

test('subscriber schedules lists month entries', function () {
    $user = subscriberUser();
    $contract = SubscribeContract::factory()->forUser($user)->create();
    $month = now()->format('Y-m');

    SubscriberSchedule::factory()->create([
        'subscribe_contract_id' => $contract->id,
        'date' => now()->startOfMonth()->addDays(2)->toDateString(),
        'dish_title' => 'Daal Chawal',
        'status' => 'scheduled',
    ]);

    $this->actingAs($user)
        ->get(route('subscriber.schedules', ['month' => $month]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('subscriber/schedules')
            ->where('month', $month)
            ->has('entries', 1)
            ->where('entries.0.dish_title', 'Daal Chawal'));
});

test('subscriber history lists delivered only', function () {
    $user = subscriberUser();
    $contract = SubscribeContract::factory()->forUser($user)->create();

    SubscriberSchedule::factory()->delivered()->create([
        'subscribe_contract_id' => $contract->id,
        'date' => now()->subDays(3)->toDateString(),
        'dish_title' => 'Delivered Meal',
    ]);
    SubscriberSchedule::factory()->create([
        'subscribe_contract_id' => $contract->id,
        'date' => now()->addDays(3)->toDateString(),
        'status' => 'scheduled',
        'dish_title' => 'Future Meal',
    ]);

    $this->actingAs($user)
        ->get(route('subscriber.history'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('subscriber/history')
            ->has('entries.data', 1)
            ->where('entries.data.0.dish_title', 'Delivered Meal'));
});

test('subscriber contract shows private pricing', function () {
    $user = subscriberUser();
    SubscribeContract::factory()->forUser($user)->create([
        'package_amount' => 500,
        'discount' => 50,
        'agreed_total' => 450,
        'persons' => 10,
        'days_of_week' => ['mon', 'wed', 'fri'],
        'status' => 'active',
    ]);

    $this->actingAs($user)
        ->get(route('subscriber.contract'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('subscriber/contract')
            ->where('contract.package_amount_formatted', 'Rs. 500')
            ->where('contract.discount_formatted', 'Rs. 50')
            ->where('contract.has_discount', true)
            ->where('contract.agreed_total_formatted', 'Rs. 450')
            ->where('contract.day_bill_formatted', 'Rs. 4,500')
            ->where('contract.days_of_week_labels', ['Mon', 'Wed', 'Fri']));
});

test('subscriber contract hides package amount and discount when no discount', function () {
    $user = subscriberUser();
    SubscribeContract::factory()->forUser($user)->create([
        'package_amount' => 450,
        'discount' => 0,
        'agreed_total' => 450,
        'persons' => 10,
        'status' => 'active',
    ]);

    $this->actingAs($user)
        ->get(route('subscriber.contract'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('contract.has_discount', false)
            ->where('contract.agreed_total_formatted', 'Rs. 450'));
});

test('subscriber login redirects to subscriber dashboard', function () {
    $user = subscriberUser();

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('subscriber.dashboard'));

    $this->assertAuthenticatedAs($user);
});
