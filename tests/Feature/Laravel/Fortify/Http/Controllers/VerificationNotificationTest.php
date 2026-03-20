<?php

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;

test('sends verification notification', function () {
    Notification::fake();

    $user = User::factory()
        ->unverified()
        ->create();
    $verificationSendRoute = route('verification.send');
    $homeRoute = route('home');

    $this->actingAs($user)
        ->post($verificationSendRoute)
        ->assertRedirect($homeRoute);

    Notification::assertSentTo($user, VerifyEmail::class);
});

test('does not send verification notification if email is verified', function () {
    Notification::fake();

    $user = User::factory()
        ->create();
    $verificationSendRoute = route('verification.send');
    $dashboardRoute = route('dashboard', absolute: false);

    $this->actingAs($user)
        ->post($verificationSendRoute)
        ->assertRedirect($dashboardRoute);

    Notification::assertNothingSent();
});
