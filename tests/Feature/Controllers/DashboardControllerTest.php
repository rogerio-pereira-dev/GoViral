<?php

use Inertia\Testing\AssertableInertia as Assert;

it('renders the dashboard page for authenticated users', function () {
    $this->actingAs(\App\Models\User::factory()->create());

    $response = $this->get(route('dashboard'));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('horizonUrl', '/'.ltrim(config('horizon.path', 'horizon'), '/'))
        );
});

it('shares custom horizon path as horizonUrl', function () {
    config(['horizon.path' => 'queue-dashboard']);

    $this->actingAs(\App\Models\User::factory()->create());

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('horizonUrl', '/queue-dashboard')
        );
});
