<?php

use App\Models\Page;
use App\Modules\Catalog\Models\Dish;
use App\Modules\Packages\Models\Package;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('robots txt disallows private paths and points to sitemap', function () {
    $response = $this->get(route('robots'));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('text/plain');

    $body = $response->getContent();
    expect($body)
        ->toContain('Disallow: /admin')
        ->toContain('Disallow: /cart')
        ->toContain('Disallow: /checkout')
        ->toContain('Sitemap: '.url('/sitemap.xml'));
});

test('sitemap includes public pages dishes and packages', function () {
    $dish = Dish::factory()->create([
        'title' => 'SEO Biryani',
        'slug' => 'seo-biryani',
        'status' => 'active',
    ]);

    $package = Package::factory()->create([
        'title' => 'SEO Package',
        'slug' => 'seo-package',
        'status' => 'active',
    ]);

    Page::query()->updateOrCreate(
        ['slug' => 'privacy-policy'],
        [
            'title' => 'Privacy Policy',
            'body' => '<p>Privacy</p>',
            'status' => 'published',
        ],
    );

    Dish::factory()->create([
        'slug' => 'inactive-seo-dish',
        'status' => 'inactive',
    ]);

    $response = $this->get(route('sitemap'));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('xml');

    $xml = $response->getContent();
    expect($xml)
        ->toContain(url('/'))
        ->toContain(url('/menu'))
        ->toContain(url('/packages'))
        ->toContain(url('/menu/'.$dish->slug))
        ->toContain(url('/packages/'.$package->slug))
        ->toContain(url('/privacy-policy'))
        ->not->toContain('inactive-seo-dish');
});

test('home shares seo defaults for public pages', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('seo.site_name')
            ->has('seo.default_description')
            ->has('seo.app_url')
            ->has('seo.organization.@type')
            ->where('seo.organization.name', fn ($name) => is_string($name) && $name !== ''));
});
