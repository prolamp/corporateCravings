<?php

use App\Models\User;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Dish;
use App\Support\Enums\UserRole;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    Storage::fake('public');
});

function actingAsCatalogManager(UserRole $role = UserRole::Admin): User
{
    $user = User::factory()->create(['status' => 'active']);
    $user->assignRole($role->value);

    return $user;
}

test('guests cannot access categories', function () {
    $this->get(route('admin.categories.index'))->assertRedirect(route('login'));
});

test('customer cannot access categories', function () {
    $customer = actingAsCatalogManager(UserRole::Customer);

    $this->actingAs($customer)
        ->get(route('admin.categories.index'))
        ->assertForbidden();
});

test('admin can list categories', function () {
    $admin = actingAsCatalogManager();
    Category::factory()->count(2)->create();

    $this->actingAs($admin)
        ->get(route('admin.categories.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/categories/index')
            ->has('categories.data', 2)
            ->where('canManage', true));
});

test('admin can create a category with auto slug and image', function () {
    $admin = actingAsCatalogManager();
    $image = UploadedFile::fake()->image('biryani.jpg', 800, 600);

    $this->actingAs($admin)
        ->post(route('admin.categories.store'), [
            'name' => 'Gourmet Dishes',
            'status' => 'active',
            'sort_order' => 5,
            'image' => $image,
        ])
        ->assertRedirect();

    $category = Category::query()->where('name', 'Gourmet Dishes')->first();

    expect($category)->not->toBeNull()
        ->and($category->slug)->toBe('gourmet-dishes')
        ->and($category->sort_order)->toBe(5)
        ->and($category->image)->not->toBeNull();

    Storage::disk('public')->assertExists($category->image);
});

test('admin can update category and replace image deleting old file', function () {
    $admin = actingAsCatalogManager();
    $category = Category::factory()->create([
        'name' => 'Old Name',
        'slug' => 'old-name',
        'image' => 'categories/old.webp',
    ]);
    Storage::disk('public')->put('categories/old.webp', 'old');

    $newImage = UploadedFile::fake()->image('new.jpg', 640, 480);

    $this->actingAs($admin)
        ->put(route('admin.categories.update', $category), [
            'name' => 'Fresh Name',
            'status' => 'active',
            'sort_order' => 1,
            'image' => $newImage,
        ])
        ->assertRedirect();

    $category->refresh();

    expect($category->name)->toBe('Fresh Name')
        ->and($category->slug)->toBe('fresh-name')
        ->and($category->image)->not->toBe('categories/old.webp');

    Storage::disk('public')->assertMissing('categories/old.webp');
    Storage::disk('public')->assertExists($category->image);
});

test('admin can toggle category status', function () {
    $admin = actingAsCatalogManager();
    $category = Category::factory()->create(['status' => 'active']);

    $this->actingAs($admin)
        ->patch(route('admin.categories.toggle-status', $category))
        ->assertRedirect();

    expect($category->fresh()->status)->toBe('inactive');
});

test('admin cannot delete a category that still has dishes', function () {
    $admin = actingAsCatalogManager();
    $category = Category::factory()->create();
    Dish::factory()->create(['category_id' => $category->id]);

    $this->actingAs($admin)
        ->delete(route('admin.categories.destroy', $category))
        ->assertRedirect()
        ->assertSessionHas('error');

    expect(Category::query()->whereKey($category->id)->exists())->toBeTrue();
});

test('admin can delete an empty category and its image', function () {
    $admin = actingAsCatalogManager();
    $category = Category::factory()->create([
        'image' => 'categories/gone.webp',
    ]);
    Storage::disk('public')->put('categories/gone.webp', 'x');

    $this->actingAs($admin)
        ->delete(route('admin.categories.destroy', $category))
        ->assertRedirect(route('admin.categories.index'));

    expect(Category::query()->whereKey($category->id)->exists())->toBeFalse();
    Storage::disk('public')->assertMissing('categories/gone.webp');
});

test('assistant can manage categories', function () {
    $assistant = actingAsCatalogManager(UserRole::Assistant);

    $this->actingAs($assistant)
        ->get(route('admin.categories.index'))
        ->assertOk();
});
