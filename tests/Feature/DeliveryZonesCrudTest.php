<?php

use App\Models\User;
use App\Modules\Delivery\Models\DeliveryZone;
use App\Support\Enums\UserRole;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

function actingAsZoneManager(UserRole $role = UserRole::Admin): User
{
    $user = User::factory()->create(['status' => 'active']);
    $user->assignRole($role->value);

    return $user;
}

test('guests cannot access delivery zones', function () {
    $this->get(route('admin.delivery-zones.index'))->assertRedirect(route('login'));
});

test('assistant cannot manage delivery zones', function () {
    $assistant = actingAsZoneManager(UserRole::Assistant);

    $this->actingAs($assistant)
        ->get(route('admin.delivery-zones.index'))
        ->assertForbidden();
});

test('admin can list delivery zones', function () {
    $admin = actingAsZoneManager();
    DeliveryZone::factory()->count(2)->create();

    $this->actingAs($admin)
        ->get(route('admin.delivery-zones.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/delivery-zones/index')
            ->has('zones.data', 2)
            ->where('canManage', true));
});

test('admin can create a delivery zone', function () {
    $admin = actingAsZoneManager();

    $this->actingAs($admin)
        ->post(route('admin.delivery-zones.store'), [
            'name' => 'F-7',
            'fee' => 300,
            'is_active' => true,
            'sort_order' => 5,
        ])
        ->assertRedirect();

    $zone = DeliveryZone::query()->where('name', 'F-7')->first();

    expect($zone)->not->toBeNull()
        ->and((float) $zone->fee)->toBe(300.0)
        ->and($zone->is_active)->toBeTrue();
});

test('admin can update and toggle a delivery zone', function () {
    $admin = actingAsZoneManager();
    $zone = DeliveryZone::factory()->create([
        'name' => 'Old Zone',
        'fee' => 100,
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->put(route('admin.delivery-zones.update', $zone), [
            'name' => 'New Zone',
            'fee' => 220,
            'is_active' => true,
            'sort_order' => 2,
        ])
        ->assertRedirect();

    expect($zone->fresh()->name)->toBe('New Zone')
        ->and((float) $zone->fresh()->fee)->toBe(220.0);

    $this->actingAs($admin)
        ->patch(route('admin.delivery-zones.toggle-status', $zone))
        ->assertRedirect();

    expect($zone->fresh()->is_active)->toBeFalse();
});

test('admin can delete an unused delivery zone', function () {
    $admin = actingAsZoneManager();
    $zone = DeliveryZone::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.delivery-zones.destroy', $zone))
        ->assertRedirect(route('admin.delivery-zones.index'));

    expect(DeliveryZone::query()->whereKey($zone->id)->exists())->toBeFalse();
});

test('manager can manage delivery zones', function () {
    $manager = actingAsZoneManager(UserRole::Manager);

    $this->actingAs($manager)
        ->get(route('admin.delivery-zones.index'))
        ->assertOk();
});
