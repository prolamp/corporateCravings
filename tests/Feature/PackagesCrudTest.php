<?php

use App\Models\User;
use App\Modules\Catalog\Models\Dish;
use App\Modules\Catalog\Models\DishVariation;
use App\Modules\Packages\Models\Package;
use App\Modules\Packages\Models\PackageItem;
use App\Support\Enums\UserRole;
use App\Support\Enums\VariationType;
use App\Support\Enums\VariationUnit;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    Storage::fake('public');
});

function actingAsPackageManager(UserRole $role = UserRole::Admin): User
{
    $user = User::factory()->create(['status' => 'active']);
    $user->assignRole($role->value);

    return $user;
}

/**
 * @return array{0: Dish, 1: Dish}
 */
function packageDishesWithSingles(): array
{
    $first = Dish::factory()->create(['title' => 'Pkg Biryani']);
    DishVariation::factory()->create([
        'dish_id' => $first->id,
        'label' => '1 Serving',
        'quantity' => 1,
        'unit' => VariationUnit::Serving->value,
        'price' => 600,
        'type' => VariationType::Single,
        'status' => 'active',
    ]);

    $second = Dish::factory()->create(['title' => 'Pkg Karahi']);
    DishVariation::factory()->create([
        'dish_id' => $second->id,
        'label' => '1 Serving',
        'quantity' => 1,
        'unit' => VariationUnit::Serving->value,
        'price' => 400,
        'type' => VariationType::Single,
        'status' => 'active',
    ]);

    return [$first, $second];
}

test('guests cannot access packages', function () {
    $this->get(route('admin.packages.index'))->assertRedirect(route('login'));
});

test('assistant cannot manage packages', function () {
    $assistant = actingAsPackageManager(UserRole::Assistant);

    $this->actingAs($assistant)
        ->get(route('admin.packages.index'))
        ->assertForbidden();
});

test('admin can list packages', function () {
    $admin = actingAsPackageManager();
    Package::factory()->count(2)->create();

    $this->actingAs($admin)
        ->get(route('admin.packages.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/packages/index')
            ->has('packages.data', 2)
            ->where('canManage', true));
});

test('admin can create a package with auto base and public total', function () {
    $admin = actingAsPackageManager();
    [$first, $second] = packageDishesWithSingles();
    $image = UploadedFile::fake()->image('combo.jpg', 800, 600);

    $this->actingAs($admin)
        ->post(route('admin.packages.store'), [
            'title' => 'Office Lunch Combo',
            'short_description' => 'Daily corporate lunch',
            'status' => 'active',
            'show_on_website' => true,
            'sort_order' => 1,
            'discount_amount' => 100,
            'discount_percent' => 0,
            'image' => $image,
            'items' => [
                ['dish_id' => $first->id],
                ['dish_id' => $second->id],
            ],
        ])
        ->assertRedirect();

    $package = Package::query()->where('title', 'Office Lunch Combo')->first();

    expect($package)->not->toBeNull()
        ->and($package->slug)->toBe('office-lunch-combo')
        ->and($package->show_on_website)->toBeTrue()
        ->and((float) $package->base_amount_per_person)->toBe(500.0)
        ->and((float) $package->discount_amount)->toBe(100.0)
        ->and((float) $package->total_amount_per_person)->toBe(400.0)
        ->and($package->items()->count())->toBe(2)
        ->and($package->image)->not->toBeNull();

    expect(Storage::disk('public')->exists($package->image))->toBeTrue();
});

test('admin can update package discounts and items', function () {
    $admin = actingAsPackageManager();
    [$first, $second] = packageDishesWithSingles();
    $package = Package::factory()->create([
        'title' => 'Old Combo',
        'base_amount_per_person' => 0,
        'discount_amount' => 0,
        'discount_percent' => 0,
        'total_amount_per_person' => 0,
    ]);
    PackageItem::query()->create([
        'package_id' => $package->id,
        'dish_id' => $first->id,
        'dish_variation_id' => $first->singleVariations()->first()->id,
        'unit_price' => 600,
        'sort_order' => 1,
    ]);

    $this->actingAs($admin)
        ->put(route('admin.packages.update', $package), [
            'title' => 'Updated Combo',
            'short_description' => 'Updated',
            'status' => 'active',
            'show_on_website' => false,
            'sort_order' => 2,
            'discount_amount' => 50,
            'discount_percent' => 10,
            'items' => [
                ['dish_id' => $first->id],
                ['dish_id' => $second->id],
            ],
        ])
        ->assertRedirect();

    $package->refresh();

    // dish sum 1000 ÷ 2 = avg base 500; amount 50 + 10% of 500 = 100; total 400
    expect($package->title)->toBe('Updated Combo')
        ->and($package->show_on_website)->toBeFalse()
        ->and((float) $package->base_amount_per_person)->toBe(500.0)
        ->and((float) $package->total_amount_per_person)->toBe(400.0)
        ->and($package->items()->count())->toBe(2);
});

test('admin can toggle package status', function () {
    $admin = actingAsPackageManager();
    $package = Package::factory()->create(['status' => 'active']);

    $this->actingAs($admin)
        ->patch(route('admin.packages.toggle-status', $package))
        ->assertRedirect();

    expect($package->fresh()->status)->toBe('inactive');
});

test('admin can delete a package without contracts', function () {
    $admin = actingAsPackageManager();
    $package = Package::factory()->create(['image' => 'packages/gone.webp']);
    Storage::disk('public')->put('packages/gone.webp', 'x');

    $this->actingAs($admin)
        ->delete(route('admin.packages.destroy', $package))
        ->assertRedirect(route('admin.packages.index'));

    expect(Package::query()->whereKey($package->id)->exists())->toBeFalse();
    expect(Storage::disk('public')->exists('packages/gone.webp'))->toBeFalse();
});

test('manager can manage packages', function () {
    $manager = actingAsPackageManager(UserRole::Manager);

    $this->actingAs($manager)
        ->get(route('admin.packages.index'))
        ->assertOk();
});
