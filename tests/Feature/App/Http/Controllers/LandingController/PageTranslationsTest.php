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
        $response = $this->withSession(['locale' => $locale])
                        ->get(route('home'));

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Landing')
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
            'validation_failed_message' => 'Validation failed. Please check the fields above.',
            'coupon_code_label' => 'Coupon code (optional)',
            'coupon_apply_cta' => 'Apply',
        ],
        'es' => [
            'title' => 'Comenzar mi crecimiento',
            'what_you_get_title' => 'Qué recibes en tu informe',
            'validation_failed_message' => 'La validación falló. Revisa los campos anteriores.',
            'coupon_code_label' => 'Código de cupón (opcional)',
            'coupon_apply_cta' => 'Aplicar',
        ],
        'pt' => [
            'title' => 'Começar meu crescimento',
            'what_you_get_title' => 'O que você recebe no relatório',
            'validation_failed_message' => 'A validação falhou. Verifique os campos acima.',
            'coupon_code_label' => 'Código do cupom (opcional)',
            'coupon_apply_cta' => 'Aplicar',
        ],
    ];

    foreach ($cases as $locale => $expected) {
        $response = $this->withSession(['locale' => $locale])
                        ->get(route('form.index'));

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Form/StartGrowth')
                ->where('locale', $locale)
                ->where('translations.title', $expected['title'])
                ->where('translations.what_you_get_title', $expected['what_you_get_title'])
                ->where('translations.validation_failed_message', $expected['validation_failed_message'])
                ->where('translations.coupon_code_label', $expected['coupon_code_label'])
                ->where('translations.coupon_apply_cta', $expected['coupon_apply_cta'])
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
        $response = $this->withSession(['locale' => $locale, 'thank_you_allowed' => true])
                        ->get(route('form.thank-you'));

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Form/ThankYou')
                ->where('translations.title', $expected['title'])
                ->where('translations.cta', $expected['cta'])
            );
    }
});
