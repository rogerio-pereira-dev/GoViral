<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * Supported locales for the application.
     *
     * @var array<int, string>
     */
    private const SUPPORTED_LOCALES = ['en', 'es', 'pt'];

    public function index(Request $request, string $locale): RedirectResponse
    {
        if (! in_array($locale, self::SUPPORTED_LOCALES, true)) {
            return redirect()->route('home');
        }

        $request->session()->put('locale', $locale);

        $previous = url()->previous();
        $appUrl   = rtrim(config('app.url'), '/');

        if (blank($previous) || ! str_starts_with($previous, $appUrl)) {
            return redirect()->route('home');
        }

        return redirect()->back();
    }
}
