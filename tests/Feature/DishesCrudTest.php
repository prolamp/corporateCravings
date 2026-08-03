<?php

use App\Models\User;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Dish;
use App\Modules\Catalog\Models\DishVariation;
use App\Support\Enums\UserRole;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    Storage::fake('public');
});

function actingAsDishManager(UserRole $role = UserRole::Admin): User
{
    $user = User::factory()->create(['status' => 'active']);
    $user->assignRole($role->value);

    return $user;
}

test('guests cannot access dishes', function () {
    $this->get(route('admin.dishes.index'))->assertRedirect(route('login'));
});

test('customer cannot access dishes', function () {
    $customer = actingAsDishManager(UserRole::Customer);

    $this->actingAs($customer)
        ->get(route('admin.dishes.index'))
        ->assertForbidden();
});

test('admin can list dishes', function () {
    $admin = actingAsDishManager();
    Dish::factory()->count(2)->create();

    $this->actingAs($admin)
        ->get(route('admin.dishes.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/dishes/index')
            ->has('dishes.data', 2)
            ->where('canManage', true));
});

test('admin can create a dish with image and variations', function () {
    $admin = actingAsDishManager();
    $category = Category::factory()->create();
    $image = UploadedFile::fake()->image('biryani.jpg', 800, 600);

    $this->actingAs($admin)
        ->post(route('admin.dishes.store'), [
            'category_id' => $category->id,
            'title' => 'Saffron Biryani',
            'short_description' => 'Aromatic basmati rice with chicken.',
            'status' => 'active',
            'sort_order' => 1,
            'image' => $image,
            'variations' => [
                [
                    'label' => '1 Serving',
                    'quantity' => 1,
                    'unit' => 'serving',
                    'price' => 600,
                    'type' => 'single',
                    'status' => 'active',
                    'sort_order' => 1,
                ],
                [
                    'label' => '5 KG',
                    'quantity' => 5,
                    'unit' => 'kg',
                    'price' => 12000,
                    'type' => 'bulk',
                    'status' => 'active',
                    'sort_order' => 2,
                ],
            ],
        ])
        ->assertRedirect();

    $dish = Dish::query()->where('title', 'Saffron Biryani')->first();

    expect($dish)->not->toBeNull()
        ->and($dish->slug)->toBe('saffron-biryani')
        ->and($dish->image)->not->toBeNull()
        ->and($dish->variations()->count())->toBe(2);

        expect(Storage::disk('public')->exists($dish->image))->toBeTrue();
});

test('admin can update dish variations and replace image', function () {
    $admin = actingAsDishManager();
    $dish = Dish::factory()->create([
        'title' => 'Old Dish',
        'image' => 'dishes/old.webp',
    ]);
    Storage::disk('public')->put('dishes/old.webp', 'old');
    $variation = DishVariation::factory()->create([
        'dish_id' => $dish->id,
        'label' => '1 Serving',
        'price' => 400,
    ]);

    $newImage = UploadedFile::fake()->image('new.jpg', 640, 480);

    $this->actingAs($admin)
        ->put(route('admin.dishes.update', $dish), [
            'category_id' => $dish->category_id,
            'title' => 'Updated Dish',
            'short_description' => 'Updated copy',
            'status' => 'active',
            'sort_order' => 2,
            'image' => $newImage,
            'variations' => [
                [
                    'id' => $variation->id,
                    'label' => '1 Serving',
                    'quantity' => 1,
                    'unit' => 'serving',
                    'price' => 550,
                    'type' => 'single',
                    'status' => 'active',
                    'sort_order' => 1,
                ],
                [
                    'label' => '8 KG',
                    'quantity' => 8,
                    'unit' => 'kg',
                    'price' => 15000,
                    'type' => 'bulk',
                    'status' => 'active',
                    'sort_order' => 2,
                ],
            ],
        ])
        ->assertRedirect();

    $dish->refresh();

    expect($dish->title)->toBe('Updated Dish')
        ->and($dish->slug)->toBe('updated-dish')
        ->and($dish->image)->not->toBe('dishes/old.webp')
        ->and($dish->variations()->count())->toBe(2)
        ->and((float) $variation->fresh()->price)->toBe(550.0);

    expect(Storage::disk('public')->exists('dishes/old.webp'))->toBeFalse()
        ->and(Storage::disk('public')->exists($dish->image))->toBeTrue();
});

test('admin can toggle dish status', function () {
    $admin = actingAsDishManager();
    $dish = Dish::factory()->create(['status' => 'active']);

    $this->actingAs($admin)
        ->patch(route('admin.dishes.toggle-status', $dish))
        ->assertRedirect();

    expect($dish->fresh()->status)->toBe('inactive');
});

test('admin can delete a dish and its image', function () {
    $admin = actingAsDishManager();
    $dish = Dish::factory()->create(['image' => 'dishes/gone.webp']);
    Storage::disk('public')->put('dishes/gone.webp', 'x');
    DishVariation::factory()->create(['dish_id' => $dish->id]);

    $this->actingAs($admin)
        ->delete(route('admin.dishes.destroy', $dish))
        ->assertRedirect(route('admin.dishes.index'));

    expect(Dish::query()->whereKey($dish->id)->exists())->toBeFalse()
        ->and(DishVariation::query()->where('dish_id', $dish->id)->exists())->toBeFalse();

    expect(Storage::disk('public')->exists('dishes/gone.webp'))->toBeFalse();
});

test('assistant can manage dishes', function () {
    $assistant = actingAsDishManager(UserRole::Assistant);

    $this->actingAs($assistant)
        ->get(route('admin.dishes.index'))
        ->assertOk();
});
