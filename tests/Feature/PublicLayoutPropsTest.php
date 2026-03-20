<?php

use Inertia\Testing\AssertableInertia as Assert;

it('shares PublicLayout props on landing page', function () {
    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Landing')
            ->where('supportedLocales', ['en', 'es', 'pt'])
            ->has('footerTagline'));
});

it('shares PublicLayout props on start-growth page', function () {
    $response = $this->withSession(['locale' => 'es'])
        ->get('/start-growth');

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Form/StartGrowth')
            ->where('locale', 'es')
            ->where('footerTagline', 'Diseñado para el crecimiento viral.')
            ->where('supportedLocales', ['en', 'es', 'pt']));
});

it('renders thank-you page with PublicLayout props when session allows it', function () {
    $response = $this->withSession(['locale' => 'pt', 'thank_you_allowed' => true])
        ->get(route('form.thank-you'));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Form/ThankYou')
            ->where('locale', 'pt')
            ->where('footerTagline', 'Feito para crescimento viral.')
            ->where('supportedLocales', ['en', 'es', 'pt']));
});
