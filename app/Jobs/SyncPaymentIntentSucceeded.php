<?php

namespace App\Jobs;

use App\Models\AnalysisRequest;
use App\Models\DiscountCoupon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class SyncPaymentIntentSucceeded implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Give the app a short window to persist the analysis request
     * after the frontend confirms the payment.
     */
    public int $tries = 6;

    /** 1 minute delay between attempts. */
    public int $backoff = 60;

    public int $timeout = 60;

    public function __construct(
        public string $paymentIntentId
    ) {
        $this->onQueue('analysis');
    }

    public function handle(): void
    {
        $analysisRequest = AnalysisRequest::where('stripe_payment_intent_id', $this->paymentIntentId)
                                ->first();

        if (! $analysisRequest) {
            throw new RuntimeException('analysis request not found for payment_intent (yet)');
        }

        $processDispatchKey = "stripe:process-analysis-dispatched:{$this->paymentIntentId}";
        // TTL for the dedupe key: a short window to cover webhook retries and
        // queued-job overlaps for this payment intent.
        $processDispatchExpiresAt = now()->addMinutes(30);

        // Cache key used to deduplicate ProcessAnalysisRequest dispatching.
        // Stripe can retry webhooks and Laravel can retry jobs; this prevents duplicate processing.
        if ($analysisRequest->payment_status === 'paid') {
            // If it is already paid, we only enqueue again when processing is still queued.
            // We then rely on the cache key to avoid double dispatch.
            $cacheHasProcessDispatchKey = Cache::has($processDispatchKey);
            $isQueued = $analysisRequest->processing_status === 'queued';

            if ($isQueued && ! $cacheHasProcessDispatchKey) {
                // If the record is still queued and the dedupe key is missing,
                // we enqueue processing and then immediately set the key.
                ProcessAnalysisRequest::dispatch($analysisRequest->id);
                Cache::put($processDispatchKey, true, $processDispatchExpiresAt);
            }

            return;
        }

        $analysisRequest->update([
            'payment_status' => 'paid',
            'processing_status' => 'queued',
        ]);

        if ($analysisRequest->discount_coupon_id !== null) {
            DiscountCoupon::whereKey($analysisRequest->discount_coupon_id)
                ->increment('times_used');
        }

        // Dispatch processing and mark it in cache so webhook/job retries don't enqueue duplicates.
        ProcessAnalysisRequest::dispatch($analysisRequest->id);
        Cache::put($processDispatchKey, true, $processDispatchExpiresAt);
    }

    public function failed(Throwable $exception): void
    {
        Log::warning('Stripe webhook sync failed', [
            'payment_intent_id' => $this->paymentIntentId,
            'error' => $exception->getMessage(),
        ]);
    }
}
