
<?php

use App\Models\User;

it('allows a user to register via the browser', function () {
    $this->markTestSkipped("Application doesn't allow Register");
});

it('allows an existing user to log in via the browser', function () {
    /** @var \App\Models\User $user */
    $user = User::factory()->create([
        'email' => 'browser-login@example.com',
        'password' => bcrypt('password'),
    ]);

    $page = visit('/login');

    $page->assertSee('Log in to your account')
        ->assertNoSmoke()
        ->fill('email', $user->email)
        ->fill('password', 'password')
        ->click('@login-button')
        ->assertSee('Dashboard')
        ->assertNoSmoke();

    $this->assertAuthenticatedAs($user);
});
