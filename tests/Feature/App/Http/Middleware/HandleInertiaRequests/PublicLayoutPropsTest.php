<?php

use Inertia\Testing\AssertableInertia as Assert;

it('shares PublicLayout props on landing page', function () {
    $homeRoute = route('home');
    $locales = [
            'en',
            'es',
            'pt',
        ];

    $this->get($homeRoute)
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page->component('Landing')
                                    ->where('supportedLocales', $locales)
                                    ->has('footerTagline')
        );
});

it('shares PublicLayout props on start-growth page', function () {
    $locales = [
            'en',
            'es',
            'pt',
        ];

    $this->withSession([
            'locale' => 'es',
        ])
        ->get('/start-growth')
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page->component('Form/StartGrowth')
                                    ->where('locale', 'es')
                                    ->where('footerTagline', 'Diseñado para el crecimiento viral.')
                                    ->where('supportedLocales', $locales)
        );
});

it('renders thank-you page with PublicLayout props when session allows it', function () {
    $thankYouRoute = route('form.thank-you');
    $locales = [
            'en',
            'es',
            'pt',
        ];

    $this->withSession([
                'locale' => 'pt',
                'thank_you_allowed' => true,
            ])
        ->get($thankYouRoute)
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page->component('Form/ThankYou')
                                    ->where('locale', 'pt')
                                    ->where('footerTagline', 'Feito para crescimento viral.')
                                    ->where('supportedLocales', $locales)
        );
});
