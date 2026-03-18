<?php

it('runs smoke checks for all public web routes without JavaScript errors', function () {
    $pages = visit([
        '/',
        '/start-growth',
        '/thank-you',
        '/locale/en',
        '/locale/it',
    ]);

    $pages->assertNoSmoke();
});

it('runs smoke checks for auth routes without JavaScript errors', function () {
    $pages = visit([
        '/login',
        '/forgot-password',
    ]);

    $pages->assertNoSmoke();
});

it('runs smoke checks for authenticated web routes without JavaScript errors', function () {
    $user = \App\Models\User::factory()->create();

    $this->actingAs($user);

    $pages = visit([
        '/core/dashboard',
        '/core/discount-coupons',
        '/core/settings',
        '/core/settings/profile',
        '/core/settings/password',
        '/core/settings/appearance',
        '/core/settings/two-factor',
    ]);

    $pages->assertNoSmoke();
});

it('redirects guest to login when visiting dashboard', function () {
    $page = visit('/core/dashboard');

    $page
        ->assertPathIs('/login')
        ->assertSee('Log in to your account')
        ->assertNoSmoke();
});
