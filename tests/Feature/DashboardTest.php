<?php

use App\Models\User;
use App\Modules\Expenses\Models\Expense;
use App\Modules\Orders\Models\Order;
use App\Support\Enums\UserRole;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('guests are redirected to the login page', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('management users can visit the admin dashboard with role widgets', function () {
    $user = User::factory()->create();
    $user->assignRole(UserRole::Admin->value);

    $order = Order::factory()->create(['status' => 'pending']);
    $order->forceFill(['amount_paid' => 1500])->save();

    Expense::factory()->create([
        'total' => 250,
        'date' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('dashboard')
            ->has('widgets')
            ->has('stats')
            ->has('recentOrders')
            ->has('todaysSchedule')
            ->where('canViewEarnings', true)
            ->where('stats.orders', 1)
            ->where('stats.expenses_mtd', 250));
});

test('manager dashboard hides earnings and expense money widgets', function () {
    $user = User::factory()->create();
    $user->assignRole(UserRole::Manager->value);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('canViewEarnings', false)
            ->where('canViewProfitLoss', false)
            ->missing('stats.earnings')
            ->missing('stats.expenses')
            ->has('stats.orders')
            ->has('stats.pending_deliveries'));
});

test('assistant dashboard only shows ops widgets', function () {
    $user = User::factory()->create();
    $user->assignRole(UserRole::Assistant->value);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('canViewEarnings', false)
            ->where('widgets', ['orders', 'pending_deliveries'])
            ->missing('stats.earnings'));
});

test('subscriber is routed to subscriber dashboard from main dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole(UserRole::Subscriber->value);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('subscriber/dashboard'));
});

test('customer is routed to customer dashboard from main dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole(UserRole::Customer->value);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('customer/dashboard'));
});
