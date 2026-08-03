<?php

use App\Models\Page;
use App\Models\User;
use App\Support\Enums\UserRole;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

function actingAsCmsStaff(UserRole $role = UserRole::Admin): User
{
    $user = User::factory()->create(['status' => 'active']);
    $user->assignRole($role->value);

    return $user;
}

test('guests cannot access cms pages', function () {
    $this->get(route('admin.pages.index'))->assertRedirect(route('login'));
});

test('assistant cannot manage cms pages', function () {
    $assistant = actingAsCmsStaff(UserRole::Assistant);

    $this->actingAs($assistant)
        ->get(route('admin.pages.index'))
        ->assertForbidden();
});

test('admin can list cms pages', function () {
    $admin = actingAsCmsStaff();
    Page::factory()->count(2)->create();

    $this->actingAs($admin)
        ->get(route('admin.pages.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/pages/index')
            ->has('pages.data', 2)
            ->where('canManage', true));
});

test('admin can create a cms page with auto slug', function () {
    $admin = actingAsCmsStaff();

    $this->actingAs($admin)
        ->post(route('admin.pages.store'), [
            'title' => 'Contact Hours',
            'slug' => null,
            'body' => '<p>We are open daily.</p>',
            'meta_title' => 'Contact Hours | Corporate Cravings',
            'meta_description' => 'Opening hours',
            'status' => 'published',
        ])
        ->assertRedirect();

    $page = Page::query()->where('title', 'Contact Hours')->first();

    expect($page)->not->toBeNull()
        ->and($page->slug)->toBe('contact-hours')
        ->and($page->status)->toBe('published');
});

test('admin can update and toggle cms page status', function () {
    $admin = actingAsCmsStaff();
    $page = Page::factory()->create([
        'title' => 'Old Title',
        'slug' => 'old-title',
        'status' => 'published',
    ]);

    $this->actingAs($admin)
        ->put(route('admin.pages.update', $page), [
            'title' => 'New Title',
            'slug' => 'new-title',
            'body' => '<p>Updated</p>',
            'meta_title' => null,
            'meta_description' => null,
            'status' => 'draft',
        ])
        ->assertRedirect();

    expect($page->fresh()->title)->toBe('New Title')
        ->and($page->fresh()->slug)->toBe('new-title')
        ->and($page->fresh()->status)->toBe('draft');

    $this->actingAs($admin)
        ->patch(route('admin.pages.toggle-status', $page))
        ->assertRedirect();

    expect($page->fresh()->status)->toBe('published');
});

test('admin can delete a cms page', function () {
    $admin = actingAsCmsStaff();
    $page = Page::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.pages.destroy', $page))
        ->assertRedirect(route('admin.pages.index'));

    expect(Page::query()->whereKey($page->id)->exists())->toBeFalse();
});

test('manager can manage cms pages', function () {
    $manager = actingAsCmsStaff(UserRole::Manager);

    $this->actingAs($manager)
        ->get(route('admin.pages.index'))
        ->assertOk();
});
