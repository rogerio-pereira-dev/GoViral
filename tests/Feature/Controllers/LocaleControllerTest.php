<?php

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Session;

uses(WithFaker::class);

it('redirects to home for unsupported locales', function () {
    $response = $this->from(route('home'))->get('/locale/it');

    $response
        ->assertRedirect(route('home'));
});

it('stores the locale in session and redirects back for supported locales', function () {
    $fromUrl = $this->faker->url();

    $response = $this->from($fromUrl)->get('/locale/en');

    $response
        ->assertRedirect($fromUrl);

    $this->assertSame('en', session('locale'));
});

