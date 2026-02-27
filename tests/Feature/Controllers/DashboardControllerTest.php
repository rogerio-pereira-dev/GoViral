<?php

use Inertia\Testing\AssertableInertia as Assert;

it('renders the dashboard page for authenticated users', function () {
    $this->actingAs(\App\Models\User::factory()->create());

    $response = $this->get(route('dashboard'));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
        );
});

