<?php

use Illuminate\Support\Facades\App;

it('keeps default locale when no session locale is set', function () {
    $default = App::getLocale();
    $homeRoute = '/';

    $this->withSession([])
        ->get($homeRoute);

    $currentLocale = App::getLocale();

    expect($currentLocale)
        ->toBe($default);
});

it('sets the locale from session when supported', function () {
    App::setLocale('en');

    $homeRoute = '/';

    $this->withSession([
            'locale' => 'pt',
        ])
        ->get($homeRoute);

    $currentLocale = App::getLocale();

    expect($currentLocale)
        ->toBe('pt');
});

it('ignores unsupported session locale and keeps current locale', function () {
    App::setLocale('en');

    $homeRoute = '/';

    $this->withSession([
            'locale' => 'it',
        ])
        ->get($homeRoute);

    $currentLocale = App::getLocale();

    expect($currentLocale)
        ->toBe('en');
});
