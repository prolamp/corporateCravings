<?php

use App\Models\User;
use App\Support\Enums\UserRole;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

function actingAsRole(UserRole $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role->value);

    return $user;
}

test('guests cannot access users index', function () {
    $this->get(route('admin.users.index'))->assertRedirect(route('login'));
});

test('admin can list users', function () {
    $admin = actingAsRole(UserRole::Admin);
    User::factory()->count(2)->create()->each->assignRole(UserRole::Customer->value);

    $this->actingAs($admin)
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/users/index')
            ->has('users.data')
            ->where('canCreate', true));
});

test('customer cannot access users index', function () {
    $customer = actingAsRole(UserRole::Customer);

    $this->actingAs($customer)
        ->get(route('admin.users.index'))
        ->assertForbidden();
});

test('super admin can create an admin user', function () {
    $super = actingAsRole(UserRole::SuperAdmin);

    $this->actingAs($super)
        ->post(route('admin.users.store'), [
            'name' => 'New Admin',
            'phone' => '03009990001',
            'email' => 'new.admin@example.com',
            'password' => 'Password1!',
            'status' => 'active',
            'role' => UserRole::Admin->value,
        ])
        ->assertRedirect();

    $created = User::query()->where('phone', '03009990001')->first();
    expect($created)->not->toBeNull()
        ->and($created->hasRole(UserRole::Admin->value))->toBeTrue();
});

test('admin cannot create another admin', function () {
    $admin = actingAsRole(UserRole::Admin);

    $this->actingAs($admin)
        ->post(route('admin.users.store'), [
            'name' => 'Blocked Admin',
            'phone' => '03009990002',
            'status' => 'active',
            'role' => UserRole::Admin->value,
            'password' => 'Password1!',
        ])
        ->assertSessionHasErrors('role');

    expect(User::query()->where('phone', '03009990002')->exists())->toBeFalse();
});

test('admin can create a manager', function () {
    $admin = actingAsRole(UserRole::Admin);

    $this->actingAs($admin)
        ->post(route('admin.users.store'), [
            'name' => 'New Manager',
            'phone' => '03009990003',
            'status' => 'active',
            'role' => UserRole::Manager->value,
        ])
        ->assertRedirect();

    $created = User::query()->where('phone', '03009990003')->first();
    expect($created)->not->toBeNull()
        ->and($created->hasRole(UserRole::Manager->value))->toBeTrue();
});

test('manager cannot create a manager', function () {
    $manager = actingAsRole(UserRole::Manager);

    $this->actingAs($manager)
        ->post(route('admin.users.store'), [
            'name' => 'Blocked Manager',
            'phone' => '03009990004',
            'status' => 'active',
            'role' => UserRole::Manager->value,
            'password' => 'Password1!',
        ])
        ->assertSessionHasErrors('role');
});

test('assistant cannot create users', function () {
    $assistant = actingAsRole(UserRole::Assistant);

    $this->actingAs($assistant)
        ->get(route('admin.users.create'))
        ->assertForbidden();
});

test('admin can update a manager but not another admin', function () {
    $admin = actingAsRole(UserRole::Admin);
    $manager = actingAsRole(UserRole::Manager);
    $otherAdmin = actingAsRole(UserRole::Admin);

    $this->actingAs($admin)
        ->put(route('admin.users.update', $manager), [
            'name' => 'Updated Manager',
            'phone' => $manager->phone,
            'email' => $manager->email,
            'status' => 'inactive',
            'role' => UserRole::Manager->value,
        ])
        ->assertRedirect();

    expect($manager->fresh()->status)->toBe('inactive')
        ->and($manager->fresh()->name)->toBe('Updated Manager');

    $this->actingAs($admin)
        ->put(route('admin.users.update', $otherAdmin), [
            'name' => 'Hijacked',
            'phone' => $otherAdmin->phone,
            'status' => 'active',
            'role' => UserRole::Admin->value,
        ])
        ->assertForbidden();
});

test('admin can delete a customer but not themselves', function () {
    $admin = actingAsRole(UserRole::Admin);
    $customer = actingAsRole(UserRole::Customer);

    $this->actingAs($admin)
        ->delete(route('admin.users.destroy', $admin))
        ->assertForbidden();

    $this->actingAs($admin)
        ->delete(route('admin.users.destroy', $customer))
        ->assertRedirect(route('admin.users.index'));

    expect(User::query()->whereKey($customer->id)->exists())->toBeFalse();
});

test('users index filters by role', function () {
    $admin = actingAsRole(UserRole::Admin);
    User::factory()->create()->assignRole(UserRole::Customer->value);
    User::factory()->create()->assignRole(UserRole::Subscriber->value);

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['role' => UserRole::Customer->value]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('filters.role', UserRole::Customer->value)
            ->has('users.data', 1));
});

test('users index hides self and above-level roles', function () {
    $admin = actingAsRole(UserRole::Admin);
    $super = actingAsRole(UserRole::SuperAdmin);
    $peerAdmin = actingAsRole(UserRole::Admin);
    $manager = actingAsRole(UserRole::Manager);
    $customer = actingAsRole(UserRole::Customer);

    $this->actingAs($admin)
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('users.data', 3)
            ->where('users.data', function ($users) use ($admin, $super, $peerAdmin, $manager, $customer) {
                $ids = collect($users)->pluck('id')->all();

                return ! in_array($admin->id, $ids, true)
                    && ! in_array($super->id, $ids, true)
                    && in_array($peerAdmin->id, $ids, true)
                    && in_array($manager->id, $ids, true)
                    && in_array($customer->id, $ids, true);
            })
            ->where('roleOptions', function ($options) {
                $values = collect($options)->pluck('value')->all();

                return ! in_array(UserRole::SuperAdmin->value, $values, true)
                    && in_array(UserRole::Admin->value, $values, true)
                    && in_array(UserRole::Customer->value, $values, true);
            }));
});

test('admin cannot view a super admin user page', function () {
    $admin = actingAsRole(UserRole::Admin);
    $super = actingAsRole(UserRole::SuperAdmin);

    $this->actingAs($admin)
        ->get(route('admin.users.show', $super))
        ->assertForbidden();
});

test('admin can toggle user active status from the list', function () {
    $admin = actingAsRole(UserRole::Admin);
    $customer = actingAsRole(UserRole::Customer);

    expect($customer->status)->toBe('active');

    $this->actingAs($admin)
        ->patch(route('admin.users.toggle-status', $customer))
        ->assertRedirect();

    expect($customer->fresh()->status)->toBe('inactive');

    $this->actingAs($admin)
        ->patch(route('admin.users.toggle-status', $customer))
        ->assertRedirect();

    expect($customer->fresh()->status)->toBe('active');
});
