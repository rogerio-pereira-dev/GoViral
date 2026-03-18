<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessAnalysisRequest;
use App\Models\AnalysisRequest;
use App\Models\DiscountCoupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\WebhookSignature;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $secret = config('cashier.webhook.secret');

        if (! is_string($secret) || blank($secret)) {
            return response()->json(['error' => 'Webhook secret not configured'], 500);
        }

        if (blank($request->header('Stripe-Signature'))) {
            return response()->json(['error' => 'Missing Stripe-Signature header'], 403);
        }

        try {
            WebhookSignature::verifyHeader(
                $request->getContent(),
                $request->header('Stripe-Signature'),
                $secret,
                (int) config('cashier.webhook.tolerance', 300)
            );
        } catch (SignatureVerificationException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }

        $payload = json_decode($request->getContent(), true);

        if (! is_array($payload) || empty($payload['type'])) {
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        $data = $payload['data'] ?? null;
        if (! is_array($data)) {
            return response()->json(['received' => true], 200);
        }

        if ($payload['type'] === 'payment_intent.succeeded') {
            $this->handlePaymentIntentSucceeded($payload);
        }

        if ($payload['type'] === 'payment_intent.payment_failed') {
            $this->handlePaymentIntentPaymentFailed($payload);
        }

        return response()->json(['received' => true], 200);
    }

    private function handlePaymentIntentSucceeded(array $payload): void
    {
        $object = $payload['data']['object'] ?? null;

        if (! is_array($object) || empty($object['id'])) {
            return;
        }

        $paymentIntentId = $object['id'];

        $analysisRequest = AnalysisRequest::where('stripe_payment_intent_id', $paymentIntentId)->first();

        if (! $analysisRequest) {
            Log::info('Stripe webhook: analysis request not found for payment_intent', [
                'payment_intent_id' => $paymentIntentId,
            ]);

            return;
        }

        if ($analysisRequest->payment_status === 'paid') {
            return;
        }

        $analysisRequest->update([
            'payment_status' => 'paid',
            'processing_status' => 'queued',
        ]);

        if ($analysisRequest->discount_coupon_id !== null) {
            DiscountCoupon::query()
                ->whereKey($analysisRequest->discount_coupon_id)
                ->increment('times_used');
        }

        ProcessAnalysisRequest::dispatch($analysisRequest->id);
    }

    private function handlePaymentIntentPaymentFailed(array $payload): void
    {
        $object = $payload['data']['object'] ?? null;

        if (! is_array($object) || empty($object['id'])) {
            return;
        }

        $paymentIntentId = $object['id'];

        $analysisRequest = AnalysisRequest::where('stripe_payment_intent_id', $paymentIntentId)->first();

        if (! $analysisRequest) {
            Log::info('Stripe webhook: analysis request not found for payment_intent (failed)', [
                'payment_intent_id' => $paymentIntentId,
            ]);

            return;
        }

        if ($analysisRequest->payment_status === 'failed') {
            return;
        }

        $analysisRequest->update([
            'payment_status' => 'failed',
            'processing_status' => 'canceled',
        ]);
    }
}
