<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('renders the dashboard page for authenticated users', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Dashboard')
            ->where('horizonUrl', '/'.ltrim(config('horizon.path', 'horizon'), '/'))
        );
});

it('shares custom horizon path as horizonUrl', function () {
    config(['horizon.path' => 'queue-dashboard']);

    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('horizonUrl', '/queue-dashboard'));
});
