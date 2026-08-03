<?php

use App\Models\Setting;
use Database\Seeders\RolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('public site shares null analytics id when unset', function () {
    Setting::query()->updateOrCreate(
        ['key' => 'analytics.ga_measurement_id'],
        ['group' => 'analytics', 'value' => '', 'type' => 'string'],
    );

    config(['services.google_analytics.measurement_id' => null]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('site.analytics.ga_measurement_id', null));
});

test('public site shares ga measurement id from site settings', function () {
    Setting::query()->updateOrCreate(
        ['key' => 'analytics.ga_measurement_id'],
        ['group' => 'analytics', 'value' => 'G-SETTINGS01', 'type' => 'string'],
    );

    config(['services.google_analytics.measurement_id' => null]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('site.analytics.ga_measurement_id', 'G-SETTINGS01'));
});

test('env ga measurement id overrides site settings', function () {
    Setting::query()->updateOrCreate(
        ['key' => 'analytics.ga_measurement_id'],
        ['group' => 'analytics', 'value' => 'G-SETTINGS01', 'type' => 'string'],
    );

    config(['services.google_analytics.measurement_id' => 'G-ENVOVERRIDE']);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('site.analytics.ga_measurement_id', 'G-ENVOVERRIDE'));
});

test('invalid ga measurement id is not shared', function () {
    Setting::query()->updateOrCreate(
        ['key' => 'analytics.ga_measurement_id'],
        ['group' => 'analytics', 'value' => 'not-valid', 'type' => 'string'],
    );

    config(['services.google_analytics.measurement_id' => 'also-bad']);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('site.analytics.ga_measurement_id', null));
});
