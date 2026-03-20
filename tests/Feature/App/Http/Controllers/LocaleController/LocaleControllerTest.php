<?php

it('redirects to home for unsupported locales', function () {
    $homeRoute = route('home');
    $localeRoute = '/locale/it';
    $this->from($homeRoute)
        ->get($localeRoute)
        ->assertRedirect($homeRoute);
});

it('stores the locale in session and redirects back for supported locales', function () {
    $fromUrl = route('form.index');

    $this->from($fromUrl)
        ->get('/locale/en')
        ->assertRedirect($fromUrl);

    $this->assertSame('en', session('locale'));
});

it('redirects to home when previous url is external to prevent open redirect', function () {
    $homeRoute = route('home');
    $localeRoute = '/locale/en';
    $this->from('https://external.example.com/phishing')
        ->get($localeRoute)
        ->assertRedirect($homeRoute);

    $this->assertSame('en', session('locale'));
});
