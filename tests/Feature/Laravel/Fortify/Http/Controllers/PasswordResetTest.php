<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

test('reset password link screen can be rendered', function () {
    $passwordRequestRoute = route('password.request');
    $response = $this->get($passwordRequestRoute);

    $response->assertOk();
});

test('reset password link can be requested', function () {
    Notification::fake();

    $user = User::factory()
                ->create();
    $passwordEmailRoute = route('password.email');

    $this->post(
            $passwordEmailRoute,
            [
                'email' => $user->email,
            ]
        );

    Notification::assertSentTo($user, ResetPassword::class);
});

test('reset password screen can be rendered', function () {
    Notification::fake();

    $user = User::factory()
                ->create();
    $passwordEmailRoute = route('password.email');

    $this->post(
            $passwordEmailRoute,
            [
                'email' => $user->email,
            ]
        );

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
        $passwordResetRoute = route('password.reset', $notification->token);
        $response = $this->get($passwordResetRoute);

        $response->assertOk();

        return true;
    });
});

test('password can be reset with valid token', function () {
    Notification::fake();

    $user = User::factory()
                ->create();
    $passwordEmailRoute = route('password.email');

    $this->post(
            $passwordEmailRoute,
            [
                'email' => $user->email,
            ]
        );

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $passwordUpdateRoute = route('password.update');
        $loginRoute = route('login');
        $response = $this->post(
                            $passwordUpdateRoute,
                            [
                                'token' => $notification->token,
                                'email' => $user->email,
                                'password' => 'password',
                                'password_confirmation' => 'password',
                            ]
                        );

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect($loginRoute);

        return true;
    });
});

test('password cannot be reset with invalid token', function () {
    $user = User::factory()
                ->create();
    $passwordUpdateRoute = route('password.update');

    $response = $this->post(
                        $passwordUpdateRoute,
                        [
                            'token' => 'invalid-token',
                            'email' => $user->email,
                            'password' => 'newpassword123',
                            'password_confirmation' => 'newpassword123',
                        ]
                    );

    $response->assertSessionHasErrors('email');
});
