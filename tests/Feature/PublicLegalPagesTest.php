<?php

use App\Models\Page;
use Database\Seeders\RolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('published legal pages render from cms', function () {
    Page::query()->updateOrCreate(
        ['slug' => 'privacy-policy'],
        [
            'title' => 'Privacy Policy',
            'body' => '<h2>Overview</h2><p>We respect your privacy.</p>',
            'meta_title' => 'Privacy Policy | Corporate Cravings',
            'status' => 'published',
        ],
    );

    $this->get(route('pages.show', 'privacy-policy'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/cms-page')
            ->where('title', 'Privacy Policy')
            ->where('slug', 'privacy-policy')
            ->where('body_html', fn ($html) => str_contains((string) $html, 'We respect your privacy.')));
});

test('draft legal pages return branded 404', function () {
    Page::query()->updateOrCreate(
        ['slug' => 'terms'],
        [
            'title' => 'Terms',
            'body' => '<p>Draft only</p>',
            'status' => 'draft',
        ],
    );

    $this->get(route('pages.show', 'terms'))
        ->assertNotFound()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/errors/404')
            ->where('status', 404));
});

test('unknown public urls render branded 404', function () {
    $this->get('/this-route-does-not-exist-xyz')
        ->assertNotFound()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/errors/404')
            ->where('status', 404)
            ->has('site.brand.name'));
});

test('all legal footer slugs are routable when published', function (string $slug, string $title) {
    Page::query()->updateOrCreate(
        ['slug' => $slug],
        [
            'title' => $title,
            'body' => '<p>'.$title.' body</p>',
            'status' => 'published',
        ],
    );

    $this->get('/'.$slug)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/cms-page')
            ->where('slug', $slug));
})->with([
    ['privacy-policy', 'Privacy Policy'],
    ['terms', 'Terms & Conditions'],
    ['delivery-policy', 'Delivery Policy'],
    ['cancellation-refund', 'Cancellation & Refund Policy'],
]);
