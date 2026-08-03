<?php

use App\Models\Setting;
use App\Models\User;
use App\Support\Enums\UserRole;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

function actingAsSettingsStaff(UserRole $role = UserRole::Admin): User
{
    $user = User::factory()->create(['status' => 'active']);
    $user->assignRole($role->value);

    return $user;
}

test('guests cannot access site settings', function () {
    $this->get(route('admin.settings.edit'))->assertRedirect(route('login'));
});

test('assistant cannot access site settings', function () {
    $assistant = actingAsSettingsStaff(UserRole::Assistant);

    $this->actingAs($assistant)
        ->get(route('admin.settings.edit'))
        ->assertForbidden();
});

test('manager cannot access site settings', function () {
    $manager = actingAsSettingsStaff(UserRole::Manager);

    $this->actingAs($manager)
        ->get(route('admin.settings.edit'))
        ->assertForbidden();
});

test('admin can view and update site settings', function () {
    $admin = actingAsSettingsStaff();

    $this->actingAs($admin)
        ->get(route('admin.settings.edit'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/settings/index')
            ->has('groups')
            ->has('values.brand.name')
            ->has('values.brand.whatsapp')
            ->has('values.analytics.ga_measurement_id'));

    $this->actingAs($admin)
        ->put(route('admin.settings.update'), [
            'values' => [
                'brand' => [
                    'name' => 'Corporate Cravings PK',
                    'phone' => '03049998888',
                    'whatsapp' => '923049998888',
                    'email' => 'hello@corporatecraving.com',
                    'address' => 'F-7 Islamabad',
                    'hours' => 'Daily 11:00–23:00',
                ],
                'analytics' => [
                    'ga_measurement_id' => 'G-TEST12345',
                ],
            ],
        ])
        ->assertRedirect();

    expect(Setting::query()->where('key', 'brand.name')->value('value'))
        ->toBe('Corporate Cravings PK')
        ->and(Setting::query()->where('key', 'brand.whatsapp')->value('value'))
        ->toBe('923049998888')
        ->and(Setting::query()->where('key', 'analytics.ga_measurement_id')->value('value'))
        ->toBe('G-TEST12345');
});

test('admin cannot save invalid ga measurement id', function () {
    $admin = actingAsSettingsStaff();

    $this->actingAs($admin)
        ->from(route('admin.settings.edit'))
        ->put(route('admin.settings.update'), [
            'values' => [
                'brand' => [
                    'name' => 'Corporate Cravings',
                    'phone' => '03041160070',
                    'whatsapp' => '923041160070',
                    'email' => 'info@corporatecraving.com',
                    'address' => 'I-9 Islamabad',
                    'hours' => 'Mon–Sat 10:00–22:00',
                ],
                'analytics' => [
                    'ga_measurement_id' => 'not-a-ga-id',
                ],
            ],
        ])
        ->assertRedirect(route('admin.settings.edit'))
        ->assertSessionHasErrors('values.analytics.ga_measurement_id');
});
