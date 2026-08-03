<?php

use App\Models\User;
use App\Modules\Expenses\Models\Expense;
use App\Modules\Expenses\Models\ExpenseCategory;
use App\Support\Enums\UserRole;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

function actingAsExpenseStaff(UserRole $role = UserRole::Admin): User
{
    $user = User::factory()->create(['status' => 'active']);
    $user->assignRole($role->value);

    return $user;
}

test('guests cannot access expenses', function () {
    $this->get(route('admin.expenses.index'))->assertRedirect(route('login'));
});

test('assistant can list and create expenses but cannot manage', function () {
    $assistant = actingAsExpenseStaff(UserRole::Assistant);
    $category = ExpenseCategory::factory()->create(['is_active' => true]);
    Expense::factory()->create(['expense_category_id' => $category->id]);

    $this->actingAs($assistant)
        ->get(route('admin.expenses.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/expenses/index')
            ->where('canCreate', true)
            ->where('canManage', false)
            ->where('canViewTotals', false)
            ->where('totals', null));

    $this->actingAs($assistant)
        ->post(route('admin.expenses.store'), [
            'expense_category_id' => $category->id,
            'title' => 'Rice bags',
            'quantity' => 10,
            'unit' => 'kg',
            'unit_price' => 250,
            'date' => now()->toDateString(),
            'status' => 'pending',
        ])
        ->assertRedirect();

    $expense = Expense::query()->where('title', 'Rice bags')->first();
    expect($expense)->not->toBeNull()
        ->and((float) $expense->total)->toBe(2500.0)
        ->and($expense->created_by)->toBe($assistant->id);

    $this->actingAs($assistant)
        ->get(route('admin.expenses.edit', $expense))
        ->assertForbidden();
});

test('admin can list expenses with filtered totals', function () {
    $admin = actingAsExpenseStaff();
    $category = ExpenseCategory::factory()->create();
    Expense::factory()->create([
        'expense_category_id' => $category->id,
        'total' => 1000,
        'status' => 'approved',
    ]);
    Expense::factory()->create([
        'expense_category_id' => $category->id,
        'total' => 500,
        'status' => 'pending',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.expenses.index', ['status' => 'approved']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/expenses/index')
            ->has('expenses.data', 1)
            ->where('canViewTotals', true)
            ->where('totals.filtered', 1000));
});

test('admin can create update and delete an expense', function () {
    $admin = actingAsExpenseStaff();
    $category = ExpenseCategory::factory()->create(['is_active' => true]);

    $this->actingAs($admin)
        ->post(route('admin.expenses.store'), [
            'expense_category_id' => $category->id,
            'title' => 'Cooking oil',
            'quantity' => 2,
            'unit' => 'litre',
            'unit_price' => 800,
            'date' => '2026-07-15',
            'status' => 'pending',
        ])
        ->assertRedirect();

    $expense = Expense::query()->where('title', 'Cooking oil')->firstOrFail();
    expect((float) $expense->total)->toBe(1600.0);

    $this->actingAs($admin)
        ->put(route('admin.expenses.update', $expense), [
            'expense_category_id' => $category->id,
            'title' => 'Cooking oil refined',
            'quantity' => 3,
            'unit' => 'litre',
            'unit_price' => 800,
            'date' => '2026-07-15',
            'status' => 'approved',
        ])
        ->assertRedirect(route('admin.expenses.show', $expense));

    expect($expense->fresh()->title)->toBe('Cooking oil refined')
        ->and((float) $expense->fresh()->total)->toBe(2400.0)
        ->and($expense->fresh()->status)->toBe('approved');

    $this->actingAs($admin)
        ->delete(route('admin.expenses.destroy', $expense))
        ->assertRedirect(route('admin.expenses.index'));

    expect(Expense::query()->whereKey($expense->id)->exists())->toBeFalse();
});

test('admin can update expense status', function () {
    $admin = actingAsExpenseStaff();
    $expense = Expense::factory()->create(['status' => 'pending']);

    $this->actingAs($admin)
        ->patch(route('admin.expenses.update-status', $expense), [
            'status' => 'approved',
        ])
        ->assertRedirect();

    expect($expense->fresh()->status)->toBe('approved');
});

test('manager can view expenses without aggregate totals', function () {
    $manager = actingAsExpenseStaff(UserRole::Manager);
    Expense::factory()->create();

    $this->actingAs($manager)
        ->get(route('admin.expenses.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('canManage', true)
            ->where('canViewTotals', false)
            ->where('totals', null));
});
