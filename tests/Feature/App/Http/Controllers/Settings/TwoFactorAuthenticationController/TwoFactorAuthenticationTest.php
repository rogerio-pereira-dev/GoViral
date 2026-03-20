<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Fortify\Features;

test('two factor settings page can be rendered', function () {
    if (! Features::canManageTwoFactorAuthentication()) {
        $this->markTestSkipped('Two-factor authentication is not enabled.');
    }

    Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
        ]);

    $user = User::factory()
                ->create();
    $passwordConfirmedAt = time();
    $twoFactorShowRoute = route('two-factor.show');

    $this->actingAs($user)
        ->withSession([
                'auth.password_confirmed_at' => $passwordConfirmedAt,
            ])
        ->get($twoFactorShowRoute)
        ->assertInertia(fn (Assert $page) => $page->component('settings/TwoFactor')
                                                ->where('twoFactorEnabled', false)
        );
});

test('two factor settings page requires password confirmation when enabled', function () {
    if (! Features::canManageTwoFactorAuthentication()) {
        $this->markTestSkipped('Two-factor authentication is not enabled.');
    }

    $user = User::factory()
                ->create();
    $twoFactorShowRoute = route('two-factor.show');
    $passwordConfirmRoute = route('password.confirm');

    Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
        ]);

    $response = $this->actingAs($user)
                    ->get($twoFactorShowRoute);

    $response->assertRedirect($passwordConfirmRoute);
});

test('two factor settings page does not requires password confirmation when disabled', function () {
    if (! Features::canManageTwoFactorAuthentication()) {
        $this->markTestSkipped('Two-factor authentication is not enabled.');
    }

    $user = User::factory()
                ->create();
    $twoFactorShowRoute = route('two-factor.show');

    Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => false,
        ]);

    $this->actingAs($user)
        ->get($twoFactorShowRoute)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('settings/TwoFactor'));
});

test('two factor settings page returns forbidden response when two factor is disabled', function () {
    if (! Features::canManageTwoFactorAuthentication()) {
        $this->markTestSkipped('Two-factor authentication is not enabled.');
    }

    config(['fortify.features' => []]);

    $user = User::factory()
                ->create();
    $passwordConfirmedAt = time();
    $twoFactorShowRoute = route('two-factor.show');

    $this->actingAs($user)
        ->withSession([
                'auth.password_confirmed_at' => $passwordConfirmedAt,
            ])
        ->get($twoFactorShowRoute)
        ->assertForbidden();
});
