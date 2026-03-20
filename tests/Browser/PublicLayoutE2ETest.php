<?php

it('shows shared header/footer + language selector on public funnel pages', function () {
    $landing = visit('/');

    $landing
        ->assertSee('GoViral')
        ->assertSee('Engineered for Viral Growth.')
        ->assertSee('EN')
        ->assertSee('ES')
        ->assertSee('PT')
        ->assertNoSmoke();

    $form = visit('/start-growth');

    $form
        ->assertSee('GoViral')
        ->assertSee('Engineered for Viral Growth.')
        ->assertSee('EN')
        ->assertSee('ES')
        ->assertSee('PT')
        ->assertNoSmoke();

    $thankYou = visit('/thank-you');

    $thankYou
        ->assertSee('GoViral')
        ->assertSee('Engineered for Viral Growth.')
        ->assertSee('EN')
        ->assertSee('ES')
        ->assertSee('PT')
        ->assertNoSmoke();
});

it('persists locale across funnel pages when switching to pt', function () {
    $page = visit('/');

    $page->assertSee('Engineered for Viral Growth.')
        ->click('PT')
        ->assertSee('Feito para crescimento viral.');

    $form = visit('/start-growth');

    $form
        ->assertSee('Feito para crescimento viral.')
        ->assertNoSmoke();
});
