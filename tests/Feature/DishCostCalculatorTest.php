<?php

use App\Models\User;
use App\Modules\Expenses\Models\Expense;
use App\Modules\Expenses\Models\ExpenseCategory;
use App\Support\Enums\UserRole;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

function actingAsCostCalculatorStaff(UserRole $role = UserRole::Admin): User
{
    $user = User::factory()->create(['status' => 'active']);
    $user->assignRole($role->value);

    return $user;
}

test('guests cannot access cost calculator', function () {
    $this->get(route('admin.cost-calculator.show'))->assertRedirect(route('login'));
});

test('assistant cannot access cost calculator', function () {
    $assistant = actingAsCostCalculatorStaff(UserRole::Assistant);

    $this->actingAs($assistant)
        ->get(route('admin.cost-calculator.show'))
        ->assertForbidden();
});

test('admin sees grocery items only not other expense categories', function () {
    $admin = actingAsCostCalculatorStaff();
    $groceries = ExpenseCategory::factory()->create(['name' => 'Groceries', 'is_active' => true]);
    $rent = ExpenseCategory::factory()->create(['name' => 'Rent', 'is_active' => true]);

    Expense::factory()->create([
        'expense_category_id' => $groceries->id,
        'title' => 'Chicken',
        'unit' => 'kg',
        'unit_price' => 800,
    ]);
    Expense::factory()->create([
        'expense_category_id' => $groceries->id,
        'title' => 'Rice',
        'unit' => 'kg',
        'unit_price' => 300,
    ]);
    Expense::factory()->create([
        'expense_category_id' => $rent->id,
        'title' => 'Shop rent',
        'unit' => null,
        'unit_price' => 50000,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.cost-calculator.show'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/cost-calculator/index')
            ->has('groceryOptions', 2)
            ->where('groceryOptions.0.title', fn ($title) => in_array($title, ['Chicken', 'Rice'], true))
            ->where('groceryOptions', fn ($options) => collect($options)->every(
                fn ($option) => ! str_contains(strtolower($option['title']), 'rent'),
            )));
});

test('admin can estimate dish cost from grocery weights', function () {
    $admin = actingAsCostCalculatorStaff();

    $this->actingAs($admin)
        ->postJson(route('admin.cost-calculator.estimate'), [
            'yield_portions' => 50,
            'lines' => [
                ['unit_price' => 800, 'quantity' => 2],
                ['unit_price' => 300, 'quantity' => 5],
                ['unit_price' => 500, 'quantity' => 0.5],
            ],
        ])
        ->assertOk()
        ->assertJson([
            'total_cost' => 3350,
            'cost_per_portion' => 67,
        ]);
});

test('manager can access cost calculator', function () {
    $manager = actingAsCostCalculatorStaff(UserRole::Manager);

    $this->actingAs($manager)
        ->get(route('admin.cost-calculator.show'))
        ->assertOk();
});
