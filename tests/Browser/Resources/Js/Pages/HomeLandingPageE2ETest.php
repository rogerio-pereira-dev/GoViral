<?php

it('shows the landing page with no javascript errors', function () {
    $page = visit('/');

    $page->assertSee('Engineered for Viral Growth.')
        ->assertNoSmoke();
});

it('allows switching locales on the landing page', function () {
    $page = visit('/');

    $page->assertSee('Engineered for Viral Growth.')
        ->click('PT')
        ->assertSee('Feito para crescimento viral.')
        ->assertNoSmoke();
});
