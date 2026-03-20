<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('confirm password screen can be rendered', function () {
    $user = User::factory()
                ->create();
    $passwordConfirmRoute = route('password.confirm');
    $response = $this->actingAs($user)
                    ->get($passwordConfirmRoute);

    $response->assertOk();

    $response->assertInertia(
        fn (Assert $page) => $page->component('auth/ConfirmPassword')
    );
});

test('password confirmation requires authentication', function () {
    $passwordConfirmRoute = route('password.confirm');
    $loginRoute = route('login');
    $response = $this->get($passwordConfirmRoute);

    $response->assertRedirect($loginRoute);
});
