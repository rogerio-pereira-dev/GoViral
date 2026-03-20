<?php

use App\Jobs\ProcessAnalysisRequest;
use App\Jobs\SyncPaymentIntentSucceeded;
use App\Models\AnalysisRequest;
use App\Models\DiscountCoupon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

it('throws when no analysis request matches the payment intent', function (): void {
    $job = new SyncPaymentIntentSucceeded('pi_missing_1');

    expect(fn () => $job->handle())
        ->toThrow(\RuntimeException::class, 'analysis request not found for payment_intent (yet)');
});

it('marks pending request paid, dispatches analysis job, and sets dedupe cache', function (): void {
    Queue::fake();
    Cache::flush();

    $request = AnalysisRequest::factory()->create([
        'stripe_payment_intent_id' => 'pi_sync_pending_1',
        'payment_status' => 'pending',
        'processing_status' => 'waiting_payment_confirmation',
        'discount_coupon_id' => null,
    ]);

    $syncJob = new SyncPaymentIntentSucceeded('pi_sync_pending_1');
    $syncJob->handle();

    $request->refresh();
    expect($request->payment_status)->toBe('paid')
        ->and($request->processing_status)->toBe('queued');

    Queue::assertPushed(ProcessAnalysisRequest::class, fn ($job) => $job->analysisRequestId === $request->id);

    $key = 'stripe:process-analysis-dispatched:pi_sync_pending_1';
    expect(Cache::has($key))->toBeTrue();
});

it('increments coupon times_used when discount_coupon_id is set', function (): void {
    Queue::fake();
    Cache::flush();

    $coupon = DiscountCoupon::factory()->create(['times_used' => 2]);
    $request = AnalysisRequest::factory()->create([
        'stripe_payment_intent_id' => 'pi_sync_coupon_1',
        'payment_status' => 'pending',
        'processing_status' => 'waiting_payment_confirmation',
        'discount_coupon_id' => $coupon->id,
    ]);

    $syncJob = new SyncPaymentIntentSucceeded('pi_sync_coupon_1');
    $syncJob->handle();

    expect($coupon->fresh()->times_used)->toBe(3);
    Queue::assertPushed(ProcessAnalysisRequest::class);
});

it('dispatches analysis when already paid and queued but dedupe key is absent', function (): void {
    Queue::fake();
    Cache::flush();

    $request = AnalysisRequest::factory()->create([
        'stripe_payment_intent_id' => 'pi_sync_paid_queued_1',
        'payment_status' => 'paid',
        'processing_status' => 'queued',
    ]);

    $syncJob = new SyncPaymentIntentSucceeded('pi_sync_paid_queued_1');
    $syncJob->handle();

    Queue::assertPushed(ProcessAnalysisRequest::class, fn ($job) => $job->analysisRequestId === $request->id);
    expect(Cache::has('stripe:process-analysis-dispatched:pi_sync_paid_queued_1'))->toBeTrue();
});

it('does not dispatch again when paid and queued and dedupe key exists', function (): void {
    Queue::fake();
    Cache::flush();

    $request = AnalysisRequest::factory()->create([
        'stripe_payment_intent_id' => 'pi_sync_deduped_1',
        'payment_status' => 'paid',
        'processing_status' => 'queued',
    ]);

    Cache::put('stripe:process-analysis-dispatched:pi_sync_deduped_1', true, now()->addMinutes(30));

    $syncJob = new SyncPaymentIntentSucceeded('pi_sync_deduped_1');
    $syncJob->handle();

    Queue::assertNotPushed(ProcessAnalysisRequest::class);
});

it('does not dispatch when paid but processing is not queued', function (): void {
    Queue::fake();
    Cache::flush();

    AnalysisRequest::factory()->create([
        'stripe_payment_intent_id' => 'pi_sync_sent_1',
        'payment_status' => 'paid',
        'processing_status' => 'sent',
    ]);

    $syncJob = new SyncPaymentIntentSucceeded('pi_sync_sent_1');
    $syncJob->handle();

    Queue::assertNotPushed(ProcessAnalysisRequest::class);
});

it('logs warning on failed', function (): void {
    Log::spy();

    $job = new SyncPaymentIntentSucceeded('pi_sync_fail_1');
    $exception = new \RuntimeException('temporary outage');
    $job->failed($exception);

    Log::shouldHaveReceived('warning')->with(
        'Stripe webhook sync failed',
        \Mockery::on(fn (array $context): bool => $context['payment_intent_id'] === 'pi_sync_fail_1'
            && $context['error'] === 'temporary outage')
    )->once();
});
