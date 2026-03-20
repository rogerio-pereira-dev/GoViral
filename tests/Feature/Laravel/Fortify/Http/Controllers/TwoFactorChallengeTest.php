<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Fortify\Features;

test('two factor challenge redirects to login when not authenticated', function () {
    if (! Features::canManageTwoFactorAuthentication()) {
        $this->markTestSkipped('Two-factor authentication is not enabled.');
    }

    $twoFactorLoginRoute = route('two-factor.login');
    $loginRoute = route('login');
    $response = $this->get($twoFactorLoginRoute);

    $response->assertRedirect($loginRoute);
});

test('two factor challenge can be rendered', function () {
    if (! Features::canManageTwoFactorAuthentication()) {
        $this->markTestSkipped('Two-factor authentication is not enabled.');
    }

    Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
        ]);

    $user = User::factory()
                ->create();
    $twoFactorSecret = encrypt('test-secret');
    $twoFactorRecoveryCodes = json_encode(['code1', 'code2']);
    $encryptedTwoFactorRecoveryCodes = encrypt($twoFactorRecoveryCodes);
    $twoFactorConfirmedAt = now();
    $loginRoute = route('login');
    $twoFactorLoginRoute = route('two-factor.login');

    $user->forceFill([
            'two_factor_secret' => $twoFactorSecret,
            'two_factor_recovery_codes' => $encryptedTwoFactorRecoveryCodes,
            'two_factor_confirmed_at' => $twoFactorConfirmedAt,
        ])
        ->save();

    $this->post(
            $loginRoute,
            [
                'email' => $user->email,
                'password' => 'password',
            ]
        );

    $this->get($twoFactorLoginRoute)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('auth/TwoFactorChallenge'));
});
