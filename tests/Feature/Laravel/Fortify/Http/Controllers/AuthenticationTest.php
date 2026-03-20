<?php

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Fortify\Features;

test('login screen can be rendered', function () {
    $loginRoute = route('login');
    $response = $this->get($loginRoute);

    $response->assertOk();
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()
                ->create();
    $loginStoreRoute = route('login.store');
    $dashboardRoute = route('dashboard', absolute: false);

    $response = $this->post(
                        $loginStoreRoute,
                        [
                            'email' => $user->email,
                            'password' => 'password',
                        ]
                    );

    $this->assertAuthenticated();
    $response->assertRedirect($dashboardRoute);
});

test('users with two factor enabled are redirected to two factor challenge', function () {
    if (! Features::canManageTwoFactorAuthentication()) {
        $this->markTestSkipped('Two-factor authentication is not enabled.');
    }

    Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
        ]);

    $user = User::factory()
                ->create();

    $twoFactorSecret                    = encrypt('test-secret');
    $twoFactorRecoveryCodes             = json_encode(['code1', 'code2']);
    $encryptedTwoFactorRecoveryCodes    = encrypt($twoFactorRecoveryCodes);
    $twoFactorConfirmedAt               = now();
    $loginRoute                         = route('login');
    $twoFactorLoginRoute                = route('two-factor.login');

    $user->forceFill([
            'two_factor_secret' => $twoFactorSecret,
            'two_factor_recovery_codes' => $encryptedTwoFactorRecoveryCodes,
            'two_factor_confirmed_at' => $twoFactorConfirmedAt,
        ])
        ->save();

    $response = $this->post(
            $loginRoute,
            [
                'email' => $user->email,
                'password' => 'password',
            ]
        );

    $response->assertRedirect($twoFactorLoginRoute);
    $response->assertSessionHas('login.id', $user->id);
    $this->assertGuest();
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()
                ->create();
    $loginStoreRoute = route('login.store');

    $this->post(
            $loginStoreRoute,
            [
                'email' => $user->email,
                'password' => 'wrong-password',
            ]
        );

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()
                ->create();

    $logoutRoute    = route('logout');
    $homeRoute      = route('home');
    $response       = $this->actingAs($user)
                            ->post($logoutRoute);

    $this->assertGuest();
    $response->assertRedirect($homeRoute);
});

test('users are rate limited', function () {
    $user = User::factory()
                ->create();

    $userIpArray        = [
                            $user->email,
                            '127.0.0.1',
                        ];
    $userIpString       = implode('|', $userIpArray);
    $userIpString       = 'login'.$userIpString;
    $loginRateKey       = md5($userIpString);
    $loginStoreRoute    = route('login.store');

    RateLimiter::increment($loginRateKey, amount: 5);

    $response = $this->post(
                        $loginStoreRoute,
                        [
                            'email' => $user->email,
                            'password' => 'wrong-password',
                        ]
                    );

    $response->assertTooManyRequests();
});
