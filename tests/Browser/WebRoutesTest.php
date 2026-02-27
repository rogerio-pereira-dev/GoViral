<?php

it('loads all public web routes without JavaScript errors', function () {
    $pages = visit([
        '/',
        '/start-growth',
        '/locale/en',
        '/locale/it',
    ]);

    $pages->assertNoSmoke();
});

it('loads authenticated web routes without JavaScript errors', function () {
    $user = \App\Models\User::factory()->create();

    $this->actingAs($user);

    $pages = visit([
        '/core/dashboard',
        '/settings',
        '/settings/profile',
        '/settings/password',
        '/settings/appearance',
        '/settings/two-factor',
    ]);

    $pages->assertNoSmoke();
});

it('allows switching locales on the landing page', function () {
    $page = visit('/');

    $page
        ->assertSee('Engineered for Viral Growth.')
        ->click('PT')
        ->assertSee('Feito para crescimento viral.')
        ->assertNoSmoke();
});
