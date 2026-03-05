<?php

it('redirects to home for unsupported locales', function () {
    $response = $this->from(route('home'))->get('/locale/it');

    $response
        ->assertRedirect(route('home'));
});

it('stores the locale in session and redirects back for supported locales', function () {
    $fromUrl = route('form.index');

    $response = $this->from($fromUrl)->get('/locale/en');

    $response
        ->assertRedirect($fromUrl);

    $this->assertSame('en', session('locale'));
});

it('redirects to home when previous url is external to prevent open redirect', function () {
    $response = $this->from('https://external.example.com/phishing')->get('/locale/en');

    $response->assertRedirect(route('home'));
    $this->assertSame('en', session('locale'));
});
