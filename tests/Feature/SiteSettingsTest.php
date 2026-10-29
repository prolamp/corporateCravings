<?php

use App\Models\Setting;
use App\Models\User;
use App\Support\Enums\UserRole;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

function actingAsSettingsStaff(UserRole $role = UserRole::Admin): User
{
    $user = User::factory()->create(['status' => 'active']);
    $user->assignRole($role->value);

    return $user;
}

function validSettingsPayload(array $overrides = []): array
{
    return array_replace_recursive([
        'values' => [
            'brand' => [
                'name' => 'Corporate Cravings PK',
                'phone' => '03049998888',
                'whatsapp' => '923049998888',
                'email' => 'hello@corporatecraving.com',
                'address' => 'F-7 Islamabad',
                'hours' => 'Daily 11:00–23:00',
            ],
            'receipt' => [
                'manager_name' => 'Ayesha Khan',
                'bank_label' => 'Meezan Bank',
                'bank_account_number' => '0123456789012',
                'description' => 'Please pay within 7 days.',
            ],
            'analytics' => [
                'ga_measurement_id' => 'G-TEST12345',
            ],
        ],
    ], $overrides);
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
            ->has('values.receipt.manager_name')
            ->has('values.receipt.bank_label')
            ->has('values.receipt.bank_account_number')
            ->has('values.receipt.description')
            ->has('signature_url')
            ->has('values.analytics.ga_measurement_id'));

    $this->actingAs($admin)
        ->put(route('admin.settings.update'), validSettingsPayload())
        ->assertRedirect();

    expect(Setting::query()->where('key', 'brand.name')->value('value'))
        ->toBe('Corporate Cravings PK')
        ->and(Setting::query()->where('key', 'brand.whatsapp')->value('value'))
        ->toBe('923049998888')
        ->and(Setting::query()->where('key', 'receipt.manager_name')->value('value'))
        ->toBe('Ayesha Khan')
        ->and(Setting::query()->where('key', 'receipt.bank_label')->value('value'))
        ->toBe('Meezan Bank')
        ->and(Setting::query()->where('key', 'receipt.bank_account_number')->value('value'))
        ->toBe('0123456789012')
        ->and(Setting::query()->where('key', 'receipt.description')->value('value'))
        ->toBe('Please pay within 7 days.')
        ->and(Setting::query()->where('key', 'analytics.ga_measurement_id')->value('value'))
        ->toBe('G-TEST12345');
});

test('admin can upload and remove receipt signature', function () {
    Storage::fake('public');
    $admin = actingAsSettingsStaff();

    $this->actingAs($admin)
        ->post(route('admin.settings.update'), array_merge(validSettingsPayload(), [
            '_method' => 'put',
            'signature' => UploadedFile::fake()->image('signature.png', 400, 160),
        ]))
        ->assertRedirect();

    $path = Setting::query()->where('key', 'receipt.signature_path')->value('value');
    expect($path)->not->toBeEmpty()
        ->and(Storage::disk('public')->exists($path))->toBeTrue();

    $this->actingAs($admin)
        ->post(route('admin.settings.update'), array_merge(validSettingsPayload([
            'values' => [
                'receipt' => [
                    'manager_name' => 'Ayesha Khan',
                ],
            ],
        ]), [
            '_method' => 'put',
            'remove_signature' => true,
        ]))
        ->assertRedirect();

    expect(Setting::query()->where('key', 'receipt.signature_path')->value('value'))
        ->toBe('')
        ->and(Storage::disk('public')->exists($path))->toBeFalse();
});

test('admin cannot save invalid ga measurement id', function () {
    $admin = actingAsSettingsStaff();

    $this->actingAs($admin)
        ->from(route('admin.settings.edit'))
        ->put(route('admin.settings.update'), validSettingsPayload([
            'values' => [
                'analytics' => [
                    'ga_measurement_id' => 'not-a-ga-id',
                ],
            ],
        ]))
        ->assertRedirect(route('admin.settings.edit'))
        ->assertSessionHasErrors('values.analytics.ga_measurement_id');
});
