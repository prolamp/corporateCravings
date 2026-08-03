<?php

use App\Models\User;
use App\Support\Enums\UserRole;
use App\Support\RoleAccess;
use Database\Seeders\RolesAndPermissionsSeeder;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('super admin has all permissions including create admin and earnings', function () {
    $user = User::factory()->create();
    $user->assignRole(UserRole::SuperAdmin->value);

    expect($user->can('users.create_admin'))->toBeTrue()
        ->and($user->can('reports.earnings'))->toBeTrue()
        ->and($user->can('reports.profit_loss'))->toBeTrue()
        ->and($user->can('payments.view'))->toBeTrue()
        ->and(RoleAccess::creatableRoles($user))->toContain(UserRole::Admin);
});

test('admin cannot create admin users but can see earnings', function () {
    $user = User::factory()->create();
    $user->assignRole(UserRole::Admin->value);

    expect($user->can('users.create_admin'))->toBeFalse()
        ->and($user->can('users.create_manager'))->toBeTrue()
        ->and($user->can('reports.earnings'))->toBeTrue()
        ->and($user->can('reports.profit_loss'))->toBeTrue();
});

test('manager cannot create managers or see earnings and profit loss', function () {
    $user = User::factory()->create();
    $user->assignRole(UserRole::Manager->value);

    expect($user->can('users.create_manager'))->toBeFalse()
        ->and($user->can('users.create_assistant'))->toBeTrue()
        ->and($user->can('reports.earnings'))->toBeFalse()
        ->and($user->can('reports.profit_loss'))->toBeFalse()
        ->and($user->can('payments.view'))->toBeTrue();
});

test('assistant cannot see payments or create assistants but can update order status and add expenses', function () {
    $user = User::factory()->create();
    $user->assignRole(UserRole::Assistant->value);

    expect($user->can('payments.view'))->toBeFalse()
        ->and($user->can('users.create_assistant'))->toBeFalse()
        ->and($user->can('orders.update_status'))->toBeTrue()
        ->and($user->can('expenses.create'))->toBeTrue()
        ->and($user->can('reports.earnings'))->toBeFalse();
});

test('subscriber and customer get portal permissions only', function () {
    $subscriber = User::factory()->create();
    $subscriber->assignRole(UserRole::Subscriber->value);

    $customer = User::factory()->create();
    $customer->assignRole(UserRole::Customer->value);

    expect($subscriber->can('portal.subscriber.dashboard'))->toBeTrue()
        ->and($subscriber->can('portal.subscriber.contract'))->toBeTrue()
        ->and($subscriber->can('dashboard.view'))->toBeFalse()
        ->and($customer->can('portal.customer.track'))->toBeTrue()
        ->and($customer->can('portal.customer.history'))->toBeTrue()
        ->and($customer->can('orders.manage'))->toBeFalse();
});

test('permission catalog is seeded', function () {
    expect(Permission::query()->count())->toBe(count(RoleAccess::allPermissionNames()));
});
