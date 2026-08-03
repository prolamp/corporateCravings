<?php

use App\Models\Setting;
use App\Models\User;
use App\Modules\System\Services\StatusErrorCatalogService;
use App\Support\Enums\UserRole;
use App\Support\ErrorCatalog;
use App\Support\StatusRegistry;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    StatusRegistry::flushOverrides();
    ErrorCatalog::flushOverrides();
});

function actingAsCatalogRole(UserRole $role): User
{
    $user = User::factory()->create(['status' => 'active']);
    $user->assignRole($role->value);

    return $user;
}

test('guests cannot open statuses catalog', function () {
    $this->get(route('admin.statuses.edit'))->assertRedirect(route('login'));
});

test('manager cannot open statuses catalog', function () {
    $manager = actingAsCatalogRole(UserRole::Manager);

    $this->actingAs($manager)
        ->get(route('admin.statuses.edit'))
        ->assertForbidden();
});

test('admin can view statuses and errors catalog', function () {
    $admin = actingAsCatalogRole(UserRole::Admin);

    $this->actingAs($admin)
        ->get(route('admin.statuses.edit'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/statuses/index')
            ->has('statuses')
            ->has('errors')
            ->where('statuses.0.group', 'order'));
});

test('admin can override a status label and registry resolves it', function () {
    $admin = actingAsCatalogRole(UserRole::Admin);

    $this->actingAs($admin)
        ->put(route('admin.statuses.labels.update'), [
            'labels' => [
                'order' => [
                    'pending' => 'Awaiting confirmation',
                ],
            ],
        ])
        ->assertRedirect();

    StatusRegistry::flushOverrides();

    expect(StatusRegistry::label('order', 'pending'))->toBe('Awaiting confirmation')
        ->and(Setting::query()->where('group', StatusErrorCatalogService::STATUS_GROUP)->where('key', 'order.pending')->exists())->toBeTrue();
});

test('saving the default status label removes the override', function () {
    $admin = actingAsCatalogRole(UserRole::Admin);

    Setting::query()->create([
        'group' => StatusErrorCatalogService::STATUS_GROUP,
        'key' => 'order.pending',
        'value' => 'Custom pending',
        'type' => 'string',
    ]);

    $this->actingAs($admin)
        ->put(route('admin.statuses.labels.update'), [
            'labels' => [
                'order' => [
                    'pending' => 'Pending',
                ],
            ],
        ])
        ->assertRedirect();

    expect(Setting::query()->where('key', 'order.pending')->exists())->toBeFalse();
});

test('admin can override an error message', function () {
    $admin = actingAsCatalogRole(UserRole::Admin);

    $this->actingAs($admin)
        ->put(route('admin.statuses.errors.update'), [
            'messages' => [
                'login_failed' => 'Wrong phone or password. Try again.',
            ],
        ])
        ->assertRedirect();

    ErrorCatalog::flushOverrides();

    expect(ErrorCatalog::message('login_failed'))->toBe('Wrong phone or password. Try again.');
});

test('assistant cannot update status labels', function () {
    $assistant = actingAsCatalogRole(UserRole::Assistant);

    $this->actingAs($assistant)
        ->put(route('admin.statuses.labels.update'), [
            'labels' => [
                'order' => ['pending' => 'Nope'],
            ],
        ])
        ->assertForbidden();
});
