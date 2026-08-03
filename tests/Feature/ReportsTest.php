<?php

use App\Models\User;
use App\Modules\Expenses\Models\Expense;
use App\Modules\Expenses\Models\ExpenseCategory;
use App\Modules\Orders\Models\Order;
use App\Modules\Subscribers\Models\SubscribeContract;
use App\Support\Enums\OrderChannel;
use App\Support\Enums\OrderType;
use App\Support\Enums\UserRole;
use App\Support\Money;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

function actingAsReportStaff(UserRole $role = UserRole::Admin): User
{
    $user = User::factory()->create(['status' => 'active']);
    $user->assignRole($role->value);

    return $user;
}

test('guests cannot access reports hub', function () {
    $this->get(route('admin.reports.index'))->assertRedirect(route('login'));
});

test('assistant cannot access any reports', function () {
    $assistant = actingAsReportStaff(UserRole::Assistant);

    $this->actingAs($assistant)->get(route('admin.reports.index'))->assertForbidden();
    $this->actingAs($assistant)->get(route('admin.reports.orders'))->assertForbidden();
    $this->actingAs($assistant)->get(route('admin.reports.contracts'))->assertForbidden();
    $this->actingAs($assistant)->get(route('admin.reports.earnings'))->assertForbidden();
    $this->actingAs($assistant)->get(route('admin.reports.profit-loss'))->assertForbidden();
});

test('manager can view orders and contracts but not finance reports', function () {
    $manager = actingAsReportStaff(UserRole::Manager);

    $this->actingAs($manager)
        ->get(route('admin.reports.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/reports/index')
            ->has('reports', 2));

    $this->actingAs($manager)->get(route('admin.reports.orders'))->assertOk();
    $this->actingAs($manager)->get(route('admin.reports.contracts'))->assertOk();
    $this->actingAs($manager)->get(route('admin.reports.earnings'))->assertForbidden();
    $this->actingAs($manager)->get(route('admin.reports.profit-loss'))->assertForbidden();
});

test('admin can view all reports with date filters and aggregates', function () {
    $admin = actingAsReportStaff();

    Order::factory()->create([
        'type' => OrderType::Individual,
        'channel' => OrderChannel::Admin,
        'status' => 'completed',
        'quoted_amount' => Money::of(1000),
        'amount_paid' => Money::of(900),
        'placed_at' => now(),
    ]);

    $category = ExpenseCategory::factory()->create(['name' => 'Ops']);
    Expense::factory()->create([
        'expense_category_id' => $category->id,
        'total' => Money::of(200),
        'date' => now()->toDateString(),
        'created_by' => $admin->id,
    ]);

    SubscribeContract::factory()->create([
        'status' => 'active',
        'agreed_total' => Money::of(5000),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.reports.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/reports/index')
            ->has('reports', 4));

    $this->actingAs($admin)
        ->get(route('admin.reports.orders', [
            'date_from' => now()->startOfMonth()->toDateString(),
            'date_to' => now()->toDateString(),
        ]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/reports/orders')
            ->where('report.summary.orders_count', 1));

    $this->actingAs($admin)
        ->get(route('admin.reports.earnings'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/reports/earnings')
            ->where('report.summary.paid_orders', 1));

    $this->actingAs($admin)
        ->get(route('admin.reports.profit-loss'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/reports/profit-loss')
            ->where('report.summary.revenue', 900)
            ->where('report.summary.expenses', 200));

    $this->actingAs($admin)
        ->get(route('admin.reports.contracts'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/reports/contracts')
            ->where('report.summary.active_count', 1));
});
