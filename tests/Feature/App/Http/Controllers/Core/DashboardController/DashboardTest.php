<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $dashboardRoute = route('dashboard');
    $loginRoute = route('login');
    $response = $this->get($dashboardRoute);

    $response->assertRedirect($loginRoute);
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()
                ->create();
    $dashboardRoute = route('dashboard');

    $this->actingAs($user);

    $response = $this->get($dashboardRoute);
    $response->assertOk();
});
