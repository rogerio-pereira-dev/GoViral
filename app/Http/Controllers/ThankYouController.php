<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class ThankYouController extends Controller
{
    public function index(): Response
    {
        $translations = [
            'title' => __('thank_you.title'),
            'message' => __('thank_you.message'),
            'cta' => __('thank_you.cta'),
        ];

        return Inertia::render('Form/ThankYou', [
            'translations' => $translations,
        ]);
    }
}
