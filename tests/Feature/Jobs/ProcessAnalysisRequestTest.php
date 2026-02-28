<?php

use App\Jobs\ProcessAnalysisRequest;
use App\Models\AnalysisRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('is configured with 12 max attempts per ADR-011', function (): void {
    $job = new ProcessAnalysisRequest('fake-id');

    expect($job->tries)->toBe(12);
});

it('is configured with 300-second backoff between retries', function (): void {
    $job = new ProcessAnalysisRequest('fake-id');

    expect($job->backoff)->toBe(300);
});

it('is configured with 300-second timeout for LLM and email', function (): void {
    $job = new ProcessAnalysisRequest('fake-id');

    expect($job->timeout)->toBe(300);
});

it('implements ShouldQueue', function (): void {
    expect(ProcessAnalysisRequest::class)
        ->toImplement(Illuminate\Contracts\Queue\ShouldQueue::class);
});

it('runs without error when analysis request exists and is paid', function (): void {
    $analysisRequest = AnalysisRequest::factory()->create([
        'payment_status' => 'paid',
        'processing_status' => 'queued',
    ]);

    $job = new ProcessAnalysisRequest($analysisRequest->id);
    $job->handle();

    expect($analysisRequest->refresh()->payment_status)->toBe('paid');
});

it('returns early when analysis request is not found', function (): void {
    $job = new ProcessAnalysisRequest('00000000-0000-0000-0000-000000000000');
    $job->handle();

    expect(AnalysisRequest::count())->toBe(0);
});

it('returns early when analysis request is not paid', function (): void {
    $analysisRequest = AnalysisRequest::factory()->create([
        'payment_status' => 'pending',
        'processing_status' => 'waiting_payment_confirmation',
    ]);

    $job = new ProcessAnalysisRequest($analysisRequest->id);
    $job->handle();

    $analysisRequest->refresh();
    expect($analysisRequest->payment_status)->toBe('pending');
});

it('is dispatched by the webhook to the default queue', function (): void {
    $analysisRequest = AnalysisRequest::factory()->create([
        'payment_status' => 'paid',
        'processing_status' => 'queued',
    ]);

    $job = new ProcessAnalysisRequest($analysisRequest->id);

    expect($job->queue)->toBeNull();
});
