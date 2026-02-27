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

        return redirect()->back();
    }
}
