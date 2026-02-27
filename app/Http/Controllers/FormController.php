<?php

namespace App\Http\Controllers;

use App\Http\Requests\Form\StoreAnalysisRequest;
use App\Models\AnalysisRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class FormController extends Controller
{
    public function index(): Response
    {
        $translations = [
            'title' => __('form.title'),
            'subtitle' => __('form.subtitle'),
            'copy_title' => __('form.copy_title'),
            'copy_lead' => __('form.copy_lead'),
            'what_you_get_title' => __('form.what_you_get_title'),
            'report_item_1' => __('form.report_item_1'),
            'report_item_2' => __('form.report_item_2'),
            'report_item_3' => __('form.report_item_3'),
            'report_item_4' => __('form.report_item_4'),
            'report_item_5' => __('form.report_item_5'),
            'report_item_6' => __('form.report_item_6'),
            'report_item_7' => __('form.report_item_7'),
            'report_item_8' => __('form.report_item_8'),
            'email_label' => __('form.email_label'),
            'email_placeholder' => __('form.email_placeholder'),
            'email_hint' => __('form.email_hint'),
            'tiktok_username_label' => __('form.tiktok_username_label'),
            'tiktok_username_placeholder' => __('form.tiktok_username_placeholder'),
            'aspiring_niche_label' => __('form.aspiring_niche_label'),
            'aspiring_niche_placeholder' => __('form.aspiring_niche_placeholder'),
            'bio_label' => __('form.bio_label'),
            'bio_placeholder' => __('form.bio_placeholder'),
            'video_url_1_label' => __('form.video_url_1_label'),
            'video_url_2_label' => __('form.video_url_2_label'),
            'video_url_3_label' => __('form.video_url_3_label'),
            'video_url_placeholder' => __('form.video_url_placeholder'),
            'notes_label' => __('form.notes_label'),
            'notes_placeholder' => __('form.notes_placeholder'),
            'submit_cta' => __('form.submit_cta'),
        ];

        return Inertia::render('Form/StartGrowth', [
            'locale' => app()->getLocale(),
            'translations' => $translations,
        ]);
    }

    public function store(StoreAnalysisRequest $request): RedirectResponse
    {
        $validatedData = $request->validated();

        AnalysisRequest::create([
            ...$validatedData,
            'tiktok_username' => $this->normalizeNotInformed($validatedData['tiktok_username'] ?? null),
            'bio' => $this->normalizeNotInformed($validatedData['bio'] ?? null),
            'video_url_1' => $this->normalizeNotInformed($validatedData['video_url_1'] ?? null),
            'video_url_2' => $this->normalizeNotInformed($validatedData['video_url_2'] ?? null),
            'video_url_3' => $this->normalizeNotInformed($validatedData['video_url_3'] ?? null),
            'locale' => app()->getLocale(),
            'payment_status' => 'pending',
        ]);

        return redirect()->route('form.thank-you');
    }

    private function normalizeNotInformed(?string $value): string
    {
        return blank($value) ? '<Not Informed>' : $value;
    }
}
