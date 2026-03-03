<?php

use App\Ai\Agents\GrowthReportAgent;
use App\Jobs\ProcessAnalysisRequest;
use App\Models\AnalysisRequest;
use App\Services\Llm\GrowthReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

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
    Queue::fake();
    GrowthReportAgent::fake(['Fake report content']);

    $analysisRequest = AnalysisRequest::factory()->create([
        'payment_status' => 'paid',
        'processing_status' => 'queued',
        'attempt_count' => 0,
    ]);

    $job = new ProcessAnalysisRequest($analysisRequest->id);
    $job->handle(app(GrowthReportService::class));

    expect(AnalysisRequest::find($analysisRequest->id))->toBeNull();
    Queue::assertPushedOn('emails', \Illuminate\Mail\SendQueuedMailable::class);
});

it('returns early when analysis request is not found', function (): void {
    $job = new ProcessAnalysisRequest('00000000-0000-0000-0000-000000000000');
    $job->handle(app(GrowthReportService::class));

    expect(AnalysisRequest::count())->toBe(0);
});

it('returns early when analysis request is not paid', function (): void {
    $analysisRequest = AnalysisRequest::factory()->create([
        'payment_status' => 'pending',
        'processing_status' => 'waiting_payment_confirmation',
    ]);

    $job = new ProcessAnalysisRequest($analysisRequest->id);
    $job->handle(app(GrowthReportService::class));

    $analysisRequest->refresh();
    expect($analysisRequest->payment_status)->toBe('pending');
});

it('records last_error and rethrows when LLM returns empty report', function (): void {
    GrowthReportAgent::fake(['']);

    $analysisRequest = AnalysisRequest::factory()->create([
        'payment_status' => 'paid',
        'processing_status' => 'queued',
        'attempt_count' => 0,
    ]);

    $job = new ProcessAnalysisRequest($analysisRequest->id);

    expect(fn () => $job->handle(app(GrowthReportService::class)))
        ->toThrow(InvalidArgumentException::class, 'LLM returned empty report');

    $analysisRequest->refresh();
    expect($analysisRequest->last_error)->toContain('empty report');
});

it('records last_error and rethrows when report generator throws', function (): void {
    GrowthReportAgent::fake(function (): never {
        throw new RuntimeException('API rate limit');
    });

    $analysisRequest = AnalysisRequest::factory()->create([
        'payment_status' => 'paid',
        'processing_status' => 'queued',
        'attempt_count' => 0,
    ]);

    $job = new ProcessAnalysisRequest($analysisRequest->id);

    expect(fn () => $job->handle(app(GrowthReportService::class)))
        ->toThrow(RuntimeException::class, 'API rate limit');

    $analysisRequest->refresh();
    expect($analysisRequest->last_error)->toBe('API rate limit');
});

it('uses queue name analysis per FDR-005', function (): void {
    $job = new ProcessAnalysisRequest('fake-id');

    expect($job->queue)->toBe('analysis');
});

it('marks record as failed and deletes it when job fails after max attempts', function (): void {
    $analysisRequest = AnalysisRequest::factory()->create([
        'payment_status' => 'paid',
        'processing_status' => 'processing',
    ]);

    $job = new ProcessAnalysisRequest($analysisRequest->id);
    $job->failed(new RuntimeException('Final failure'));

    expect(AnalysisRequest::find($analysisRequest->id))->toBeNull();
});
