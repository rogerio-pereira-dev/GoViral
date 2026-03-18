<?php

namespace App\Http\Controllers;

use App\Http\Requests\Form\StoreAnalysisRequest;
use App\Models\AnalysisRequest;
use App\Models\DiscountCoupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
            'validation_failed_message' => __('form.validation_failed_message'),
            'coupon_code_label' => __('form.coupon_code_label'),
            'coupon_apply_cta' => __('form.coupon_apply_cta'),
            'coupon_invalid' => __('form.coupon_invalid'),
            'coupon_applied_hint' => __('form.coupon_applied_hint'),
        ];

        return Inertia::render('Form/StartGrowth', [
            'locale' => app()->getLocale(),
            'translations' => $translations,
            'turnstileSiteKey' => config('services.turnstile.key'),
        ]);
    }

    public function paymentIntent(Request $request): JsonResponse
    {
        $baseCents = (int) config('services.stripe.price_in_cents');
        $currency = config('services.stripe.currency');
        $publishableKey = config('cashier.key');
        $secretKey = config('cashier.secret');

        if (! is_string($publishableKey) || blank($publishableKey) || ! is_string($secretKey) || blank($secretKey)) {
            return response()->json([
                'message' => __('form.payment_init_error'),
            ], 500);
        }

        $couponCode = $request->query('coupon_code');
        $coupon = null;

        if (is_string($couponCode) && trim($couponCode) !== '') {
            $coupon = DiscountCoupon::findValidByCode($couponCode);

            if (! $coupon) {
                return response()->json([
                    'message' => __('form.coupon_invalid'),
                ], 422);
            }
        }

        $amountCents = $baseCents;

        if ($coupon !== null) {
            $amountCents = DiscountCoupon::discountedAmountCents($baseCents, $coupon->value);
        }

        $discountCouponId = '';

        if ($coupon !== null) {
            $discountCouponId = $coupon->id;
        }

        $metadata = [
            'goviral_base_cents' => (string) $baseCents,
            'discount_coupon_id' => $discountCouponId,
        ];

        try {
            $paymentIntent = Cashier::stripe()->paymentIntents->create([
                'amount' => $amountCents,
                'currency' => $currency,
                'automatic_payment_methods' => ['enabled' => true],
                'metadata' => $metadata,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => __('form.payment_init_error'),
            ], 500);
        }

        $discountPercent = null;

        if ($coupon !== null) {
            $discountPercent = $coupon->value;
        }

        return response()->json([
            'paymentIntentId' => $paymentIntent->id,
            'clientSecret' => $paymentIntent->client_secret,
            'publishableKey' => $publishableKey,
            'amountCents' => $amountCents,
            'currency' => $currency,
            'discountPercent' => $discountPercent,
        ]);
    }

    public function store(StoreAnalysisRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $paymentIntentId = $validatedData['payment_intent_id'];
        $baseCents = (int) config('services.stripe.price_in_cents');

        try {
            $paymentIntent = Cashier::stripe()->paymentIntents->retrieve($paymentIntentId);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => __('form.payment_confirm_error'),
            ], 422);
        }

        $meta = [];

        if (is_object($paymentIntent->metadata)) {
            $meta = $paymentIntent->metadata->toArray();
        }

        $metaBase = '';

        if (array_key_exists('goviral_base_cents', $meta)) {
            $metaBase = $meta['goviral_base_cents'];
        }

        if ($metaBase !== (string) $baseCents) {
            return response()->json([
                'message' => __('form.payment_confirm_error'),
            ], 422);
        }

        $discountCouponId = '';

        if (array_key_exists('discount_coupon_id', $meta)) {
            $discountCouponId = $meta['discount_coupon_id'];
        }

        if (is_string($discountCouponId) && $discountCouponId !== '') {
            $coupon = DiscountCoupon::withTrashed()->find($discountCouponId);

            if (! $coupon) {
                return response()->json([
                    'message' => __('form.coupon_invalid'),
                ], 422);
            }

            $expectedCents = DiscountCoupon::discountedAmountCents($baseCents, $coupon->value);

            if ((int) $paymentIntent->amount !== $expectedCents) {
                return response()->json([
                    'message' => __('form.payment_confirm_error'),
                ], 422);
            }
        } elseif ((int) $paymentIntent->amount !== $baseCents) {
            return response()->json([
                'message' => __('form.payment_confirm_error'),
            ], 422);
        }

        $normalizedDiscountCouponId = null;

        if (is_string($discountCouponId) && $discountCouponId !== '') {
            $normalizedDiscountCouponId = $discountCouponId;
        }

        $analysisRequest = AnalysisRequest::create([
            ...$validatedData,
            'tiktok_username' => $this->normalizeNotInformed($validatedData['tiktok_username'] ?? null),
            'bio' => $this->normalizeNotInformed($validatedData['bio'] ?? null),
            'video_url_1' => $this->normalizeNotInformed($validatedData['video_url_1'] ?? null),
            'video_url_2' => $this->normalizeNotInformed($validatedData['video_url_2'] ?? null),
            'video_url_3' => $this->normalizeNotInformed($validatedData['video_url_3'] ?? null),
            'locale' => app()->getLocale(),
            'stripe_payment_intent_id' => $paymentIntentId,
            'payment_status' => 'pending',
            'processing_status' => 'waiting_payment_confirmation',
            'discount_coupon_id' => $normalizedDiscountCouponId,
        ]);

        $request->session()->put('thank_you_allowed', true);

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
