<?php

use App\Models\Page;
use Database\Seeders\RolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('faq page is public and shows accordion items', function () {
    $this->get(route('faq'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/faq')
            ->has('items')
            ->where('items', fn ($items) => count($items) >= 1)
            ->has('site.brand.name'));
});

test('faq page parses published cms faq headings', function () {
    Page::query()->updateOrCreate(
        ['slug' => 'faq'],
        [
            'title' => 'FAQ',
            'body' => '<h3>Do you deliver on weekends?</h3><p>Weekend delivery is available by arrangement.</p><h3>How do I pay?</h3><p>Website orders use cash on delivery.</p>',
            'meta_title' => 'FAQ | Corporate Cravings',
            'status' => 'published',
        ],
    );

    $this->get(route('faq'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/faq')
            ->has('items', 2)
            ->where('items.0.question', 'Do you deliver on weekends?')
            ->where('items.1.question', 'How do I pay?'));
});
