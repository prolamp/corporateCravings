<?php

use App\Models\User;
use App\Modules\Addons\Models\Addon;
use App\Modules\Catalog\Models\Dish;
use App\Support\Enums\UserRole;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    Storage::fake('public');
});

function actingAsAddonManager(UserRole $role = UserRole::Admin): User
{
    $user = User::factory()->create(['status' => 'active']);
    $user->assignRole($role->value);

    return $user;
}

test('guests cannot access addons', function () {
    $this->get(route('admin.addons.index'))->assertRedirect(route('login'));
});

test('assistant cannot manage addons', function () {
    $assistant = actingAsAddonManager(UserRole::Assistant);

    $this->actingAs($assistant)
        ->get(route('admin.addons.index'))
        ->assertForbidden();
});

test('admin can list addons', function () {
    $admin = actingAsAddonManager();
    Addon::factory()->count(2)->create();

    $this->actingAs($admin)
        ->get(route('admin.addons.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/addons/index')
            ->has('addons.data', 2)
            ->where('canManage', true));
});

test('admin can create a global addon with image', function () {
    $admin = actingAsAddonManager();
    $image = UploadedFile::fake()->image('raita.jpg', 400, 400);

    $this->actingAs($admin)
        ->post(route('admin.addons.store'), [
            'title' => 'Shahi Raita',
            'short_description' => 'Creamy yogurt side',
            'type' => 'sauce',
            'price' => 80,
            'is_global' => true,
            'status' => 'active',
            'sort_order' => 1,
            'image' => $image,
        ])
        ->assertRedirect();

    $addon = Addon::query()->where('title', 'Shahi Raita')->first();

    expect($addon)->not->toBeNull()
        ->and($addon->slug)->toBe('shahi-raita')
        ->and($addon->is_global)->toBeTrue()
        ->and($addon->image)->not->toBeNull();

    expect(Storage::disk('public')->exists($addon->image))->toBeTrue();
});

test('admin can create a dish-linked addon', function () {
    $admin = actingAsAddonManager();
    $dish = Dish::factory()->create();

    $this->actingAs($admin)
        ->post(route('admin.addons.store'), [
            'title' => 'Extra Naan',
            'type' => 'extra',
            'price' => 40,
            'is_global' => false,
            'status' => 'active',
            'dish_ids' => [$dish->id],
        ])
        ->assertRedirect();

    $addon = Addon::query()->where('title', 'Extra Naan')->first();

    expect($addon)->not->toBeNull()
        ->and($addon->is_global)->toBeFalse()
        ->and($addon->dishes()->pluck('dishes.id')->all())->toBe([$dish->id]);
});

test('admin can toggle and delete an addon', function () {
    $admin = actingAsAddonManager();
    $addon = Addon::factory()->create([
        'status' => 'active',
        'image' => 'addons/gone.webp',
    ]);
    Storage::disk('public')->put('addons/gone.webp', 'x');

    $this->actingAs($admin)
        ->patch(route('admin.addons.toggle-status', $addon))
        ->assertRedirect();

    expect($addon->fresh()->status)->toBe('inactive');

    $this->actingAs($admin)
        ->delete(route('admin.addons.destroy', $addon))
        ->assertRedirect(route('admin.addons.index'));

    expect(Addon::query()->whereKey($addon->id)->exists())->toBeFalse();
    expect(Storage::disk('public')->exists('addons/gone.webp'))->toBeFalse();
});

test('manager can manage addons', function () {
    $manager = actingAsAddonManager(UserRole::Manager);

    $this->actingAs($manager)
        ->get(route('admin.addons.index'))
        ->assertOk();
});
