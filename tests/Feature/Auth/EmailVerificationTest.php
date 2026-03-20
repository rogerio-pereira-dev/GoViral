<?php

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;

test('email verification screen can be rendered', function () {
    $user = User::factory()
                ->unverified()
                ->create();
    $verificationNoticeRoute = route('verification.notice');
    $response = $this->actingAs($user)
                    ->get($verificationNoticeRoute);

    $response->assertOk();
});

test('email can be verified', function () {
    $user = User::factory()
                ->unverified()
                ->create();

    Event::fake();

    $verificationExpiresAt = now()
                                ->addMinutes(60);
    $verificationHash = sha1($user->email);
    $verificationRouteData = [
                                'id' => $user->id,
                                'hash' => $verificationHash,
                            ];
    $verificationUrl = URL::temporarySignedRoute(
                                'verification.verify',
                                $verificationExpiresAt,
                                $verificationRouteData
                            );
    $dashboardRoute = route('dashboard', absolute: false).'?verified=1';

    $response = $this->actingAs($user)
                    ->get($verificationUrl);

    Event::assertDispatched(Verified::class);
    $freshUser = $user->fresh();
    $hasVerifiedEmail = $freshUser->hasVerifiedEmail();
    expect($hasVerifiedEmail)
        ->toBeTrue();
    $response->assertRedirect($dashboardRoute);
});

test('email is not verified with invalid hash', function () {
    $user = User::factory()
                ->unverified()
                ->create();

    Event::fake();

    $verificationExpiresAt = now()
                                ->addMinutes(60);
    $verificationHash = sha1('wrong-email');
    $verificationRouteData = [
                                'id' => $user->id,
                                'hash' => $verificationHash,
                            ];
    $verificationUrl = URL::temporarySignedRoute(
                                'verification.verify',
                                $verificationExpiresAt,
                                $verificationRouteData
                            );

    $this->actingAs($user)
        ->get($verificationUrl);

    Event::assertNotDispatched(Verified::class);
    $freshUser = $user->fresh();
    $hasVerifiedEmail = $freshUser->hasVerifiedEmail();
    expect($hasVerifiedEmail)
        ->toBeFalse();
});

test('email is not verified with invalid user id', function () {
    $user = User::factory()
                ->unverified()
                ->create();

    Event::fake();

    $verificationExpiresAt = now()
                                ->addMinutes(60);
    $verificationHash = sha1($user->email);
    $verificationRouteData = [
                                'id' => 123,
                                'hash' => $verificationHash,
                            ];
    $verificationUrl = URL::temporarySignedRoute(
                                'verification.verify',
                                $verificationExpiresAt,
                                $verificationRouteData
                            );

    $this->actingAs($user)
        ->get($verificationUrl);

    Event::assertNotDispatched(Verified::class);
    
    $freshUser = $user->fresh();
    $hasVerifiedEmail = $freshUser->hasVerifiedEmail();

    expect($hasVerifiedEmail)
        ->toBeFalse();
});

test('verified user is redirected to dashboard from verification prompt', function () {
    $user = User::factory()
                ->create();
    $verificationNoticeRoute = route('verification.notice');
    $dashboardRoute = route('dashboard', absolute: false);

    Event::fake();

    $response = $this->actingAs($user)
                    ->get($verificationNoticeRoute);

    Event::assertNotDispatched(Verified::class);
    $response->assertRedirect($dashboardRoute);
});

test('already verified user visiting verification link is redirected without firing event again', function () {
    $user = User::factory()
                ->create();

    Event::fake();

    $verificationExpiresAt = now()
                                ->addMinutes(60);
    $verificationHash = sha1($user->email);
    $verificationRouteData = [
                                'id' => $user->id,
                                'hash' => $verificationHash,
                            ];
    $verificationUrl = URL::temporarySignedRoute(
                                'verification.verify',
                                $verificationExpiresAt,
                                $verificationRouteData
                            );
    $dashboardRoute = route('dashboard', absolute: false).'?verified=1';

    $this->actingAs($user)
        ->get($verificationUrl)
        ->assertRedirect($dashboardRoute);

    Event::assertNotDispatched(Verified::class);
    $freshUser = $user->fresh();
    $hasVerifiedEmail = $freshUser->hasVerifiedEmail();
    expect($hasVerifiedEmail)
        ->toBeTrue();
});
