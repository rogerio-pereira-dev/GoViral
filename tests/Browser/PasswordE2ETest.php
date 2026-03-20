<?php

use App\Models\User;
use Illuminate\Support\Facades\Password;

it('allows requesting a password reset link via the browser', function () {
    $user = User::factory()->create([
        'email' => 'browser-forgot@example.com',
    ]);

    $page = visit('/forgot-password');

    $page->assertSee('Forgot password')
        ->assertNoSmoke()
        ->fill('email', $user->email)
        ->click('@email-password-reset-link-button')
        ->assertNoSmoke();
});

it('allows resetting the password via the browser with a valid token', function () {
    $user = User::factory()->create([
        'email' => 'browser-reset@example.com',
        'password' => bcrypt('old-password'),
    ]);

    $token = Password::broker()->createToken($user);

    $resetUrl = route('password.reset', ['token' => $token, 'email' => $user->email], absolute: false);

    $page = visit($resetUrl);

    $page->assertSee('Reset password')
        ->assertNoSmoke()
        ->fill('password', 'new-password-123')
        ->fill('password_confirmation', 'new-password-123')
        ->click('@reset-password-button')
        ->assertSee('Log in to your account')
        ->assertNoSmoke();
});
