<?php

use Database\Seeders\RolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('about page is public and uses stitch design assets', function () {
    $this->get(route('about'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/about')
            ->where('hero_image_url', fn ($url) => str_contains((string) $url, '/images/about/hero.'))
            ->where('story_image_url', fn ($url) => str_contains((string) $url, '/images/about/journey.'))
            ->where('partner_image_url', fn ($url) => str_contains((string) $url, '/images/about/partner.'))
            ->has('values', 3)
            ->has('story_stats', 2)
            ->has('impact_stats', 3)
            ->has('site.brand.name')
            ->has('site.whatsapp_url'));
});
