<?php

namespace App\Http\Controllers;

use App\Http\Requests\Form\StoreAnalysisRequest;
use App\Models\AnalysisRequest;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Cashier\Cashier;

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
            'payment_title' => __('form.payment_title'),
            'payment_description' => __('form.payment_description'),
            'payment_card_label' => __('form.payment_card_label'),
            'payment_submit_cta' => __('form.payment_submit_cta'),
            'payment_processing_cta' => __('form.payment_processing_cta'),
            'payment_init_error' => __('form.payment_init_error'),
            'payment_confirm_error' => __('form.payment_confirm_error'),
            'payment_declined_error' => __('form.payment_declined_error'),
            'payment_insufficient_funds_error' => __('form.payment_insufficient_funds_error'),
            'payment_amount_label' => __('form.payment_amount_label'),
        ];

        return Inertia::render('Form/StartGrowth', [
            'locale' => app()->getLocale(),
            'translations' => $translations,
            'turnstileSiteKey' => config('services.turnstile.key'),
        ]);
    }

    public function paymentIntent(): JsonResponse
    {
        $amountCents = config('services.stripe.price_in_cents');
        $currency = config('services.stripe.currency');
        $publishableKey = config('cashier.key');
        $secretKey = config('cashier.secret');

        if (! is_string($publishableKey) || blank($publishableKey) || ! is_string($secretKey) || blank($secretKey)) {
            return response()->json([
                'message' => __('form.payment_init_error'),
            ], 500);
        }

        try {
            $paymentIntent = Cashier::stripe()->paymentIntents->create([
                'amount' => $amountCents,
                'currency' => $currency,
                'automatic_payment_methods' => ['enabled' => true],
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => __('form.payment_init_error'),
            ], 500);
        }

        return response()->json([
            'paymentIntentId' => $paymentIntent->id,
            'clientSecret' => $paymentIntent->client_secret,
            'publishableKey' => $publishableKey,
            'amountCents' => $amountCents,
            'currency' => $currency,
        ]);
    }

    public function store(StoreAnalysisRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        $analysisRequest = AnalysisRequest::create([
            ...$validatedData,
            'tiktok_username' => $this->normalizeNotInformed($validatedData['tiktok_username'] ?? null),
            'bio' => $this->normalizeNotInformed($validatedData['bio'] ?? null),
            'video_url_1' => $this->normalizeNotInformed($validatedData['video_url_1'] ?? null),
            'video_url_2' => $this->normalizeNotInformed($validatedData['video_url_2'] ?? null),
            'video_url_3' => $this->normalizeNotInformed($validatedData['video_url_3'] ?? null),
            'locale' => app()->getLocale(),
            'stripe_payment_intent_id' => $validatedData['payment_intent_id'],
            'payment_status' => 'pending',
            'processing_status' => 'waiting_payment_confirmation',
        ]);

        return response()->json([
            'analysisRequestId' => $analysisRequest->id,
            'thankYouUrl' => route('form.thank-you'),
        ]);
    }

    private function normalizeNotInformed(?string $value): string
    {
        return blank($value) ? '<Not Informed>' : $value;
    }
}
