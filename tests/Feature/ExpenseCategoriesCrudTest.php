<?php

use App\Models\User;
use App\Modules\Expenses\Models\Expense;
use App\Modules\Expenses\Models\ExpenseCategory;
use App\Support\Enums\UserRole;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

function actingAsExpenseCategoryStaff(UserRole $role = UserRole::Admin): User
{
    $user = User::factory()->create(['status' => 'active']);
    $user->assignRole($role->value);

    return $user;
}

test('guests cannot access expense categories', function () {
    $this->get(route('admin.expense-categories.index'))->assertRedirect(route('login'));
});

test('assistant cannot manage expense categories', function () {
    $assistant = actingAsExpenseCategoryStaff(UserRole::Assistant);

    $this->actingAs($assistant)
        ->get(route('admin.expense-categories.index'))
        ->assertForbidden();
});

test('admin can list expense categories', function () {
    $admin = actingAsExpenseCategoryStaff();
    ExpenseCategory::factory()->count(2)->create();

    $this->actingAs($admin)
        ->get(route('admin.expense-categories.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/expense-categories/index')
            ->has('categories.data', 2)
            ->where('canManage', true));
});

test('admin can create an expense category', function () {
    $admin = actingAsExpenseCategoryStaff();

    $this->actingAs($admin)
        ->post(route('admin.expense-categories.store'), [
            'name' => 'Office Supplies',
            'description' => 'Paper and stationery',
            'is_active' => true,
        ])
        ->assertRedirect();

    $category = ExpenseCategory::query()->where('name', 'Office Supplies')->first();

    expect($category)->not->toBeNull()
        ->and($category->description)->toBe('Paper and stationery')
        ->and($category->is_active)->toBeTrue();
});

test('admin can update and toggle an expense category', function () {
    $admin = actingAsExpenseCategoryStaff();
    $category = ExpenseCategory::factory()->create([
        'name' => 'Old Category',
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->put(route('admin.expense-categories.update', $category), [
            'name' => 'New Category',
            'description' => 'Updated',
            'is_active' => true,
        ])
        ->assertRedirect();

    expect($category->fresh()->name)->toBe('New Category')
        ->and($category->fresh()->description)->toBe('Updated');

    $this->actingAs($admin)
        ->patch(route('admin.expense-categories.toggle-status', $category))
        ->assertRedirect();

    expect($category->fresh()->is_active)->toBeFalse();
});

test('admin can delete an unused expense category', function () {
    $admin = actingAsExpenseCategoryStaff();
    $category = ExpenseCategory::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.expense-categories.destroy', $category))
        ->assertRedirect(route('admin.expense-categories.index'));

    expect(ExpenseCategory::query()->whereKey($category->id)->exists())->toBeFalse();
});

test('admin cannot delete an expense category that has expenses', function () {
    $admin = actingAsExpenseCategoryStaff();
    $category = ExpenseCategory::factory()->create();
    Expense::factory()->create(['expense_category_id' => $category->id]);

    $this->actingAs($admin)
        ->from(route('admin.expense-categories.show', $category))
        ->delete(route('admin.expense-categories.destroy', $category))
        ->assertRedirect(route('admin.expense-categories.show', $category))
        ->assertSessionHas('error');

    expect(ExpenseCategory::query()->whereKey($category->id)->exists())->toBeTrue();
});

test('manager can manage expense categories', function () {
    $manager = actingAsExpenseCategoryStaff(UserRole::Manager);

    $this->actingAs($manager)
        ->get(route('admin.expense-categories.index'))
        ->assertOk();
});
