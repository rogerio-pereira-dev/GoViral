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

it('runs smoke checks for authenticated web routes without JavaScript errors', function () {
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
