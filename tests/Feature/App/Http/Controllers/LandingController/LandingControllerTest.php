<?php

use Inertia\Testing\AssertableInertia as Assert;

it('renders the landing page with expected shared data', function () {
    $homeRoute = route('home');
    $response = $this->get($homeRoute);

    $locale = app()->getLocale();
    $supportedLocales = [
                            'en',
                            'es',
                            'pt',
                        ];

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Landing')
                                                ->where('locale', $locale)
                                                ->where('supportedLocales', $supportedLocales)
                                                ->has('translations')
        );
});
