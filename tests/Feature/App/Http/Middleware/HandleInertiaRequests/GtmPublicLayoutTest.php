<?php

it('injects GTM snippet in HTML when services.gtm.id is configured', function () {
    config(['services.gtm.id' => 'GTM-UNITTEST']);
    $homeRoute = '/';

    $this->get($homeRoute)
        ->assertOk()
        ->assertSee('GTM-UNITTEST')
        ->assertSee('https://www.googletagmanager.com/ns.html?id=GTM-UNITTEST')
        ->assertSee('googletagmanager.com/gtm.js?id=')
        ->assertSee('+i+dl');
});

it('does not inject GTM snippet when services.gtm.id is empty', function () {
    config([
            'services.gtm.id' => '',
        ]);
    $homeRoute = '/';

    $this->get($homeRoute)
        ->assertOk()
        ->assertDontSee('googletagmanager.com/gtm.js?id=')
        ->assertDontSee('googletagmanager.com/ns.html?id=');
});
