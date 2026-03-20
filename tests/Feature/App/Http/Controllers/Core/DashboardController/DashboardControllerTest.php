<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('renders the dashboard page for authenticated users', function () {
    $user = User::factory()->create();
    $dashboardRoute = route('dashboard');

    $this->actingAs($user);

    $response = $this->get($dashboardRoute);

    $horizonPath = config('horizon.path', 'horizon');
    $horizonUrl = '/'.ltrim($horizonPath, '/');

    $response
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page->component('Dashboard')
                                    ->where('horizonUrl', $horizonUrl)
        );
});

it('shares custom horizon path as horizonUrl', function () {
    config(['horizon.path' => 'queue-dashboard']);

    $user = User::factory()->create();
    $dashboardRoute = route('dashboard');

    $this->actingAs($user);

    $this->get($dashboardRoute)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('horizonUrl', '/queue-dashboard'));
});
