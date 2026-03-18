<?php

namespace Tests\Feature\Controllers;

use App\Jobs\ProcessAnalysisRequest;
use App\Jobs\SyncPaymentIntentSucceeded;
use App\Models\AnalysisRequest;
use App\Models\DiscountCoupon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config([
        'cashier.webhook.secret' => 'whsec_test_secret',
        'cashier.webhook.tolerance' => 300,
    ]);
});

it('rejects request without valid signature with 403', function (): void {
    $payload = ['type' => 'payment_intent.succeeded', 'data' => ['object' => ['id' => 'pi_xxx']]];

    $response = $this->postJson(route('stripe.webhook'), $payload, [
        'Stripe-Signature' => 'invalid',
    ]);

    $response->assertStatus(403);
});

it('rejects request with wrong signature with 403', function (): void {
    $payload = ['type' => 'payment_intent.succeeded', 'data' => ['object' => ['id' => 'pi_xxx']]];
    $body = json_encode($payload);
    $header = stripeWebhookSignature($body, 'wrong_secret');

    $response = $this->call(
        'POST',
        route('stripe.webhook'),
        [],
        [],
        [],
        ['CONTENT_TYPE' => 'application/json', 'HTTP_STRIPE_SIGNATURE' => $header],
        $body
    );

    $response->assertStatus(403);
});

it('returns 403 when Stripe-Signature header is missing', function (): void {
    $body = json_encode(['type' => 'payment_intent.succeeded', 'data' => ['object' => ['id' => 'pi_xxx']]]);

    $response = $this->call(
        'POST',
        route('stripe.webhook'),
        [],
        [],
        [],
        ['CONTENT_TYPE' => 'application/json'],
        $body
    );

    $response->assertStatus(403)->assertJson(['error' => 'Missing Stripe-Signature header']);
});

it('returns 400 when body is not valid JSON', function (): void {
    $body = 'not valid json';
    $header = stripeWebhookSignature($body, config('cashier.webhook.secret'));

    $response = $this->call(
        'POST',
        route('stripe.webhook'),
        [],
        [],
        [],
        ['CONTENT_TYPE' => 'application/json', 'HTTP_STRIPE_SIGNATURE' => $header],
        $body
    );

    $response->assertStatus(400)->assertJson(['error' => 'Invalid payload']);
});

it('returns 200 when payload has type but data is not an array', function (): void {
    $payload = ['type' => 'payment_intent.succeeded', 'data' => 'invalid'];
    $body = json_encode($payload);
    $header = stripeWebhookSignature($body, config('cashier.webhook.secret'));

    $response = $this->call(
        'POST',
        route('stripe.webhook'),
        [],
        [],
        [],
        ['CONTENT_TYPE' => 'application/json', 'HTTP_STRIPE_SIGNATURE' => $header],
        $body
    );

    $response->assertStatus(200)->assertJson(['received' => true]);
});

it('returns 500 when webhook secret is not configured', function (): void {
    config(['cashier.webhook.secret' => null]);

    $response = $this->postJson(route('stripe.webhook'), ['type' => 'ping']);

    $response->assertStatus(500)->assertJson(['error' => 'Webhook secret not configured']);
});

it('returns 400 when payload has no type', function (): void {
    $body = '{}';
    $header = stripeWebhookSignature($body, config('cashier.webhook.secret'));

    $response = $this->call(
        'POST',
        route('stripe.webhook'),
        [],
        [],
        [],
        ['CONTENT_TYPE' => 'application/json', 'HTTP_STRIPE_SIGNATURE' => $header],
        $body
    );

    $response->assertStatus(400)->assertJson(['error' => 'Invalid payload']);
});

it('returns 200 and skips update when payment_intent.succeeded has no object id', function (): void {
    Queue::fake();

    $payload = [
        'type' => 'payment_intent.succeeded',
        'data' => ['object' => []],
    ];
    $body = json_encode($payload);
    $header = stripeWebhookSignature($body, config('cashier.webhook.secret'));

    $response = $this->call(
        'POST',
        route('stripe.webhook'),
        [],
        [],
        [],
        ['CONTENT_TYPE' => 'application/json', 'HTTP_STRIPE_SIGNATURE' => $header],
        $body
    );

    $response->assertStatus(200)->assertJson(['received' => true]);
    expect(AnalysisRequest::count())->toBe(0);
    Queue::assertNotPushed(ProcessAnalysisRequest::class);
});

it('dispatches a sync job when payment_intent.succeeded has no matching analysis request yet', function (): void {
    Queue::fake();

    $payload = [
        'type' => 'payment_intent.succeeded',
        'data' => [
            'object' => [
                'id' => 'pi_live_race_1',
            ],
        ],
    ];
    $body = json_encode($payload);
    $stripeConfig = config('cashier.webhook.secret');
    $header = stripeWebhookSignature($body, $stripeConfig);

    $response = $this->call(
        'POST',
        route('stripe.webhook'),
        [],
        [],
        [],
        [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_STRIPE_SIGNATURE' => $header,
        ],
        $body
    );

    $response->assertStatus(200)
        ->assertJson([
            'received' => true,
        ]);

    Queue::assertPushed(SyncPaymentIntentSucceeded::class, fn ($job) => $job->paymentIntentId === 'pi_live_race_1');
    Queue::assertNotPushed(ProcessAnalysisRequest::class);
});

it('returns 200 and skips update when payment_intent.payment_failed has no object id', function (): void {
    $payload = [
        'type' => 'payment_intent.payment_failed',
        'data' => [],
    ];
    $body = json_encode($payload);
    $header = stripeWebhookSignature($body, config('cashier.webhook.secret'));

    $response = $this->call(
        'POST',
        route('stripe.webhook'),
        [],
        [],
        [],
        ['CONTENT_TYPE' => 'application/json', 'HTTP_STRIPE_SIGNATURE' => $header],
        $body
    );

    $response->assertStatus(200)->assertJson(['received' => true]);
});

it('updates record and dispatches job on payment_intent.succeeded', function (): void {
    Queue::fake();

    $analysisRequest = AnalysisRequest::factory()->create([
        'stripe_payment_intent_id' => 'pi_live_123',
        'payment_status' => 'pending',
        'processing_status' => 'waiting_payment_confirmation',
    ]);

    $payload = [
        'type' => 'payment_intent.succeeded',
        'data' => [
            'object' => [
                'id' => 'pi_live_123',
            ],
        ],
    ];
    $body = json_encode($payload);
    $header = stripeWebhookSignature($body, config('cashier.webhook.secret'));

    $response = $this->call(
        'POST',
        route('stripe.webhook'),
        [],
        [],
        [],
        ['CONTENT_TYPE' => 'application/json', 'HTTP_STRIPE_SIGNATURE' => $header],
        $body
    );

    $response->assertStatus(200)->assertJson(['received' => true]);

    $analysisRequest->refresh();
    expect($analysisRequest->payment_status)->toBe('paid')
        ->and($analysisRequest->processing_status)->toBe('queued');

    Queue::assertPushed(ProcessAnalysisRequest::class, fn ($job) => $job->analysisRequestId === $analysisRequest->id);
});

it('increments coupon times_used when analysis request has discount_coupon_id', function (): void {
    Queue::fake();

    $coupon = DiscountCoupon::factory()->create(['times_used' => 0]);

    $analysisRequest = AnalysisRequest::factory()->create([
        'stripe_payment_intent_id' => 'pi_coupon_1',
        'payment_status' => 'pending',
        'processing_status' => 'waiting_payment_confirmation',
        'discount_coupon_id' => $coupon->id,
    ]);

    $payload = [
        'type' => 'payment_intent.succeeded',
        'data' => ['object' => ['id' => 'pi_coupon_1']],
    ];
    $body = json_encode($payload);
    $header = stripeWebhookSignature($body, config('cashier.webhook.secret'));

    $this->call(
        'POST',
        route('stripe.webhook'),
        [],
        [],
        [],
        ['CONTENT_TYPE' => 'application/json', 'HTTP_STRIPE_SIGNATURE' => $header],
        $body
    )->assertStatus(200);

    expect($coupon->fresh()->times_used)->toBe(1);
});

it('handles retry idempotently when record already paid', function (): void {
    Queue::fake();

    AnalysisRequest::factory()->create([
        'stripe_payment_intent_id' => 'pi_live_456',
        'payment_status' => 'paid',
        'processing_status' => 'queued',
    ]);

    $payload = [
        'type' => 'payment_intent.succeeded',
        'data' => ['object' => ['id' => 'pi_live_456']],
    ];
    $body = json_encode($payload);
    $header = stripeWebhookSignature($body, config('cashier.webhook.secret'));

    $response = $this->call(
        'POST',
        route('stripe.webhook'),
        [],
        [],
        [],
        ['CONTENT_TYPE' => 'application/json', 'HTTP_STRIPE_SIGNATURE' => $header],
        $body
    );

    $response->assertStatus(200);

    Queue::assertNotPushed(ProcessAnalysisRequest::class);
});

it('returns 200 when payment_intent_id not found and does not update', function (): void {
    Queue::fake();

    $payload = [
        'type' => 'payment_intent.succeeded',
        'data' => ['object' => ['id' => 'pi_nonexistent']],
    ];
    $body = json_encode($payload);
    $header = stripeWebhookSignature($body, config('cashier.webhook.secret'));

    $response = $this->call(
        'POST',
        route('stripe.webhook'),
        [],
        [],
        [],
        ['CONTENT_TYPE' => 'application/json', 'HTTP_STRIPE_SIGNATURE' => $header],
        $body
    );

    $response->assertStatus(200);

    expect(AnalysisRequest::count())->toBe(0);
    Queue::assertNotPushed(ProcessAnalysisRequest::class);
});

it('returns 200 for unknown event type without processing', function (): void {
    $payload = ['type' => 'customer.subscription.deleted', 'data' => ['object' => []]];
    $body = json_encode($payload);
    $header = stripeWebhookSignature($body, config('cashier.webhook.secret'));

    $response = $this->call(
        'POST',
        route('stripe.webhook'),
        [],
        [],
        [],
        ['CONTENT_TYPE' => 'application/json', 'HTTP_STRIPE_SIGNATURE' => $header],
        $body
    );

    $response->assertStatus(200)->assertJson(['received' => true]);
});

it('sets payment failed and canceled on payment_intent.payment_failed', function (): void {
    Queue::fake();

    $analysisRequest = AnalysisRequest::factory()->create([
        'stripe_payment_intent_id' => 'pi_live_fail_123',
        'payment_status' => 'pending',
        'processing_status' => 'waiting_payment_confirmation',
    ]);

    $payload = [
        'type' => 'payment_intent.payment_failed',
        'data' => ['object' => ['id' => 'pi_live_fail_123']],
    ];
    $body = json_encode($payload);
    $header = stripeWebhookSignature($body, config('cashier.webhook.secret'));

    $response = $this->call(
        'POST',
        route('stripe.webhook'),
        [],
        [],
        [],
        ['CONTENT_TYPE' => 'application/json', 'HTTP_STRIPE_SIGNATURE' => $header],
        $body
    );

    $response->assertStatus(200);

    $analysisRequest->refresh();
    expect($analysisRequest->payment_status)->toBe('failed')
        ->and($analysisRequest->processing_status)->toBe('canceled');

    Queue::assertNotPushed(ProcessAnalysisRequest::class);
});

it('handles payment_failed idempotently when record already failed', function (): void {
    AnalysisRequest::factory()->create([
        'stripe_payment_intent_id' => 'pi_live_fail_456',
        'payment_status' => 'failed',
        'processing_status' => 'canceled',
    ]);

    $payload = [
        'type' => 'payment_intent.payment_failed',
        'data' => ['object' => ['id' => 'pi_live_fail_456']],
    ];
    $body = json_encode($payload);
    $header = stripeWebhookSignature($body, config('cashier.webhook.secret'));

    $response = $this->call(
        'POST',
        route('stripe.webhook'),
        [],
        [],
        [],
        ['CONTENT_TYPE' => 'application/json', 'HTTP_STRIPE_SIGNATURE' => $header],
        $body
    );

    $response->assertStatus(200);
});

it('returns 200 when payment_intent_id not found on payment_failed', function (): void {
    $payload = [
        'type' => 'payment_intent.payment_failed',
        'data' => ['object' => ['id' => 'pi_nonexistent_fail']],
    ];
    $body = json_encode($payload);
    $header = stripeWebhookSignature($body, config('cashier.webhook.secret'));

    $response = $this->call(
        'POST',
        route('stripe.webhook'),
        [],
        [],
        [],
        ['CONTENT_TYPE' => 'application/json', 'HTTP_STRIPE_SIGNATURE' => $header],
        $body
    );

    $response->assertStatus(200);
});

function stripeWebhookSignature(string $payload, string $secret): string
{
    $timestamp = time();
    $signed = $timestamp.'.'.$payload;

    return 't='.$timestamp.',v1='.hash_hmac('sha256', $signed, $secret);
}
