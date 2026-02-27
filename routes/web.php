<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

$supportedLocales = ['en', 'es', 'pt'];

Route::get('/locale/{locale}', function (string $locale) use ($supportedLocales) {
    if (! in_array($locale, $supportedLocales, true)) {
        return redirect()->route('home');
    }
    session()->put('locale', $locale);

    return redirect()->back();
})->name('locale.switch');

Route::get('/', function () use ($supportedLocales) {
    $translations = [
        'app_name' => __('landing.app_name'),
        'tagline' => __('landing.tagline'),
        'tagline_before' => __('landing.tagline_before'),
        'tagline_highlight' => __('landing.tagline_highlight'),
        'subheadline' => __('landing.subheadline'),
        'hero_support' => __('landing.hero_support'),
        'cta_primary' => __('landing.cta_primary'),
        'cta_blueprint' => __('landing.cta_blueprint'),
        'delivery_in_minutes' => __('landing.delivery_in_minutes'),
        'section_algorithm_title' => __('landing.section_algorithm_title'),
        'section_algorithm_before' => __('landing.section_algorithm_before'),
        'section_algorithm_highlight' => __('landing.section_algorithm_highlight'),
        'section_algorithm_lead' => __('landing.section_algorithm_lead'),
        'section_algorithm_cta' => __('landing.section_algorithm_cta'),
        'section_how_title' => __('landing.section_how_title'),
        'section_how_step1' => __('landing.section_how_step1'),
        'section_how_step1_desc' => __('landing.section_how_step1_desc'),
        'section_how_step2' => __('landing.section_how_step2'),
        'section_how_step2_desc' => __('landing.section_how_step2_desc'),
        'section_how_step3' => __('landing.section_how_step3'),
        'section_how_step3_desc' => __('landing.section_how_step3_desc'),
        'section_what_you_get_title' => __('landing.section_what_you_get_title'),
        'report_summary' => __('landing.report_summary'),
        'report_score' => __('landing.report_score'),
        'report_niche' => __('landing.report_niche'),
        'report_username' => __('landing.report_username'),
        'report_bio' => __('landing.report_bio'),
        'report_profile_tips' => __('landing.report_profile_tips'),
        'report_content_ideas' => __('landing.report_content_ideas'),
        'report_30_plan' => __('landing.report_30_plan'),
        'report_viral_tips' => __('landing.report_viral_tips'),
        'report_action_plan' => __('landing.report_action_plan'),
        'section_pains_title' => __('landing.section_pains_title'),
        'section_pains_lead' => __('landing.section_pains_lead'),
        'pain_monetization' => __('landing.pain_monetization'),
        'pain_viral' => __('landing.pain_viral'),
        'pain_planning' => __('landing.pain_planning'),
        'pain_strategy' => __('landing.pain_strategy'),
        'pain_algorithm' => __('landing.pain_algorithm'),
        'pain_no_time' => __('landing.pain_no_time'),
        'section_final_title' => __('landing.section_final_title'),
        'section_final_lead' => __('landing.section_final_lead'),
        'section_final_cta' => __('landing.section_final_cta'),
        'footer_tagline' => __('landing.footer_tagline'),
        'footer_price' => __('landing.footer_price'),
    ];

    return Inertia::render('Landing', [
        'locale' => app()->getLocale(),
        'supportedLocales' => $supportedLocales,
        'translations' => $translations,
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
