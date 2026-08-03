<?php

use App\Models\User;
use App\Support\Enums\UserRole;
use App\Support\RoleAccess;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('resolves primary role helpers on the user model', function () {
    $user = User::factory()->create();
    $user->assignRole(UserRole::Manager->value);

    expect($user->primaryRoleName())->toBe('manager')
        ->and($user->primaryRole())->toBe(UserRole::Manager)
        ->and($user->primaryRoleLabel())->toBe('Manager')
        ->and($user->isManager())->toBeTrue()
        ->and($user->isManagement())->toBeTrue()
        ->and($user->isSubscriber())->toBeFalse()
        ->and($user->allows('dashboard.view'))->toBeTrue();
});

it('builds a role payload for inertia', function () {
    $user = User::factory()->create();
    $user->assignRole(UserRole::Admin->value);

    $payload = $user->rolePayload();

    expect($payload['role'])->toBe('admin')
        ->and($payload['role_label'])->toBe('Admin')
        ->and($payload['roles'])->toContain('admin')
        ->and($payload['is_admin'])->toBeTrue()
        ->and($payload['is_management'])->toBeTrue()
        ->and($payload['permissions'])->not->toBeEmpty()
        ->and($payload['dashboard_route'])->toBe('dashboard');
});

it('exposes global role helper functions', function () {
    $user = User::factory()->create();
    $user->assignRole(UserRole::Subscriber->value);

    $this->actingAs($user);

    expect(user_role())->toBe(UserRole::Subscriber)
        ->and(user_role_name())->toBe('subscriber')
        ->and(user_role_label())->toBe('Subscriber')
        ->and(user_has_role(UserRole::Subscriber))->toBeTrue()
        ->and(user_has_any_role(UserRole::Admin, UserRole::Subscriber))->toBeTrue()
        ->and(user_is_management())->toBeFalse()
        ->and(user_dashboard_route())->toBe('subscriber.dashboard')
        ->and(RoleAccess::roleOf($user))->toBe(UserRole::Subscriber);
});
