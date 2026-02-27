<?php

use Inertia\Testing\AssertableInertia as Assert;

it('renders the landing page with expected shared data', function () {
    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Landing')
            ->where('locale', app()->getLocale())
            ->where('supportedLocales', ['en', 'es', 'pt'])
            ->has('translations')
        );
});

