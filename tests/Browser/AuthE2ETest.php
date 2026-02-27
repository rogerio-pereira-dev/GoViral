
<?php

use App\Models\User;

it('allows a user to register via the browser', function () {
    $page = visit('/register');

    $page
        ->assertSee('Create an account')
        ->assertNoSmoke()
        ->fill('name', 'Browser Test User')
        ->fill('email', 'browser-user@example.com')
        ->fill('password', 'password')
        ->fill('password_confirmation', 'password')
        ->click('@register-user-button')
        ->assertSee('Dashboard')
        ->assertNoSmoke();

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['email' => 'browser-user@example.com']);
});

it('allows an existing user to log in via the browser', function () {
    /** @var \App\Models\User $user */
    $user = User::factory()->create([
        'email' => 'browser-login@example.com',
        'password' => bcrypt('password'),
    ]);

    $page = visit('/login');

    $page
        ->assertSee('Log in to your account')
        ->assertNoSmoke()
        ->fill('email', $user->email)
        ->fill('password', 'password')
        ->click('@login-button')
        ->assertSee('Dashboard')
        ->assertNoSmoke();

    $this->assertAuthenticatedAs($user);
});
