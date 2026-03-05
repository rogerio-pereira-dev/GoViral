<?php

use Inertia\Testing\AssertableInertia as Assert;

it('renders landing page translations for supported locales', function () {
    $cases = [
        'en' => [
            'tagline' => 'Engineered for Viral Growth.',
            'cta_primary' => 'Start My Growth',
        ],
        'es' => [
            'tagline' => 'Diseñado para el crecimiento viral.',
            'cta_primary' => 'Comenzar mi crecimiento',
        ],
        'pt' => [
            'tagline' => 'Feito para crescimento viral.',
            'cta_primary' => 'Começar meu crescimento',
        ],
    ];

    foreach ($cases as $locale => $expected) {
        $response = $this
            ->withSession(['locale' => $locale])
            ->get(route('home'));

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Landing')
                ->where('locale', $locale)
                ->where('translations.tagline', $expected['tagline'])
                ->where('translations.cta_primary', $expected['cta_primary'])
            );
    }
});

it('renders form page translations for supported locales', function () {
    $cases = [
        'en' => [
            'title' => 'Start My Growth',
            'what_you_get_title' => 'What you get in your report',
        ],
        'es' => [
            'title' => 'Comenzar mi crecimiento',
            'what_you_get_title' => 'Qué recibes en tu informe',
        ],
        'pt' => [
            'title' => 'Começar meu crescimento',
            'what_you_get_title' => 'O que você recebe no relatório',
        ],
    ];

    foreach ($cases as $locale => $expected) {
        $response = $this
            ->withSession(['locale' => $locale])
            ->get(route('form.index'));

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Form/StartGrowth')
                ->where('locale', $locale)
                ->where('translations.title', $expected['title'])
                ->where('translations.what_you_get_title', $expected['what_you_get_title'])
            );
    }
});

it('renders thank you page translations for supported locales', function () {
    $cases = [
        'en' => [
            'title' => 'Thank you! Your request is confirmed.',
            'cta' => 'Back to Home',
        ],
        'es' => [
            'title' => '¡Gracias! Tu solicitud está confirmada.',
            'cta' => 'Volver al inicio',
        ],
        'pt' => [
            'title' => 'Obrigado! Sua solicitação foi confirmada.',
            'cta' => 'Voltar para início',
        ],
    ];

    foreach ($cases as $locale => $expected) {
        $response = $this
            ->withSession(['locale' => $locale, 'thank_you_allowed' => true])
            ->get(route('form.thank-you'));

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Form/ThankYou')
                ->where('translations.title', $expected['title'])
                ->where('translations.cta', $expected['cta'])
            );
    }
});
