<?php

use Illuminate\Support\Facades\App;

it('keeps default locale when no session locale is set', function () {
    $default = App::getLocale();

    $this->withSession([])->get('/');

    expect(App::getLocale())->toBe($default);
});

it('sets the locale from session when supported', function () {
    App::setLocale('en');

    $this->withSession(['locale' => 'pt'])->get('/');

    expect(App::getLocale())->toBe('pt');
});

it('ignores unsupported session locale and keeps current locale', function () {
    App::setLocale('en');

    $this->withSession(['locale' => 'it'])->get('/');

    expect(App::getLocale())->toBe('en');
});
