<?php

use App\Ai\Agents\GrowthReportAgent;
use App\Jobs\ProcessAnalysisRequest;
use App\Models\AnalysisRequest;
use App\Services\Llm\GrowthReportService;
use Illuminate\Mail\SendQueuedMailable;
use Illuminate\Support\Facades\Queue;

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

    $analysisRequest->refresh();
    expect($analysisRequest->processing_status)->toBe('sent')
        ->and($analysisRequest->report_html)->toContain('Fake report content')
        ->and($analysisRequest->sent_at)->not()->toBeNull();

    Queue::assertPushedOn('emails', SendQueuedMailable::class);
});

it('returns early when analysis request is not found', function (): void {
    $job = new ProcessAnalysisRequest('00000000-0000-0000-0000-000000000000');
    $job->handle(app(GrowthReportService::class));

    expect(AnalysisRequest::count())->toBe(0);
});

it('returns early when analysis request id is not a valid UUID', function (): void {
    $job = new ProcessAnalysisRequest('not-a-valid-uuid');
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

it('reuses existing report_html on retry without calling LLM again', function (): void {
    Queue::fake();
    GrowthReportAgent::fake(['New content that should not be used']);

    $analysisRequest = AnalysisRequest::factory()->create([
        'payment_status' => 'paid',
        'processing_status' => 'queued',
        'attempt_count' => 0,
        'report_html' => 'Persisted report content',
        'sent_at' => null,
    ]);

    $job = new ProcessAnalysisRequest($analysisRequest->id);
    $job->handle(app(GrowthReportService::class));

    $analysisRequest->refresh();

    expect($analysisRequest->report_html)->toBe('Persisted report content')
        ->and($analysisRequest->processing_status)->toBe('sent')
        ->and($analysisRequest->sent_at)->not()->toBeNull();

    Queue::assertPushedOn('emails', SendQueuedMailable::class);
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

it('marks record as failed and deletes it when job fails after max attempts', function (): void {
    $analysisRequest = AnalysisRequest::factory()->create([
        'payment_status' => 'paid',
        'processing_status' => 'processing',
    ]);

    $job = new ProcessAnalysisRequest($analysisRequest->id);
    $job->failed(new RuntimeException('Final failure'));

    expect(AnalysisRequest::find($analysisRequest->id))->toBeNull();
});
