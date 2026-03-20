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

    $analysisRequest = AnalysisRequest::factory()
                            ->create([
                                'payment_status' => 'paid',
                                'processing_status' => 'queued',
                                'attempt_count' => 0,
                            ]);

    $job                 = new ProcessAnalysisRequest($analysisRequest->id);
    $growthReportService = app(GrowthReportService::class);
    $job->handle($growthReportService);

    $analysisRequest->refresh();
    $processingStatus = $analysisRequest->processing_status;
    $reportHtml       = $analysisRequest->report_html;
    $sentAt           = $analysisRequest->sent_at;

    expect($processingStatus)
        ->toBe('sent')
        ->and($reportHtml)
        ->toContain('Fake report content')
        ->and($sentAt)
        ->not()
        ->toBeNull();

    Queue::assertPushedOn('emails', SendQueuedMailable::class);
});

it('returns early when analysis request is not found', function (): void {
    $job                 = new ProcessAnalysisRequest('00000000-0000-0000-0000-000000000000');
    $growthReportService = app(GrowthReportService::class);
    $job->handle($growthReportService);

    $analysisRequestsCount = AnalysisRequest::count();
    expect($analysisRequestsCount)
        ->toBe(0);
});

it('returns early when analysis request id is not a valid UUID', function (): void {
    $job                 = new ProcessAnalysisRequest('not-a-valid-uuid');
    $growthReportService = app(GrowthReportService::class);
    $job->handle($growthReportService);

    $analysisRequestsCount = AnalysisRequest::count();
    expect($analysisRequestsCount)
        ->toBe(0);
});

it('returns early when analysis request is not paid', function (): void {
    $analysisRequest = AnalysisRequest::factory()
                            ->create([
                                'payment_status' => 'pending',
                                'processing_status' => 'waiting_payment_confirmation',
                            ]);

    $job                 = new ProcessAnalysisRequest($analysisRequest->id);
    $growthReportService = app(GrowthReportService::class);
    $job->handle($growthReportService);

    $analysisRequest->refresh();
    $paymentStatus = $analysisRequest->payment_status;
    expect($paymentStatus)
        ->toBe('pending');
});

it('records last_error and rethrows when LLM returns empty report', function (): void {
    GrowthReportAgent::fake(['']);

    $analysisRequest = AnalysisRequest::factory()
                            ->create([
                                'payment_status' => 'paid',
                                'processing_status' => 'queued',
                                'attempt_count' => 0,
                            ]);

    $job                 = new ProcessAnalysisRequest($analysisRequest->id);
    $growthReportService = app(GrowthReportService::class);
    $executeJob          = fn () => $job->handle($growthReportService);

    expect($executeJob)
        ->toThrow(
            InvalidArgumentException::class,
            'LLM returned empty report'
        );

    $analysisRequest->refresh();
    $lastError = $analysisRequest->last_error;
    expect($lastError)
        ->toContain('empty report');
});

it('reuses existing report_html on retry without calling LLM again', function (): void {
    Queue::fake();
    GrowthReportAgent::fake(['New content that should not be used']);

    $analysisRequest = AnalysisRequest::factory()
                            ->create([
                                    'payment_status' => 'paid',
                                    'processing_status' => 'queued',
                                    'attempt_count' => 0,
                                    'report_html' => 'Persisted report content',
                                    'sent_at' => null,
                                ]);

    $job                 = new ProcessAnalysisRequest($analysisRequest->id);
    $growthReportService = app(GrowthReportService::class);
    $job->handle($growthReportService);

    $analysisRequest->refresh();

    $reportHtml       = $analysisRequest->report_html;
    $processingStatus = $analysisRequest->processing_status;
    $sentAt           = $analysisRequest->sent_at;

    expect($reportHtml)
        ->toBe('Persisted report content')
        ->and($processingStatus)
        ->toBe('sent')
        ->and($sentAt)
        ->not()
        ->toBeNull();

    Queue::assertPushedOn('emails', SendQueuedMailable::class);
});

it('records last_error and rethrows when report generator throws', function (): void {
    GrowthReportAgent::fake(function (): never {
        throw new RuntimeException('API rate limit');
    });

    $analysisRequest = AnalysisRequest::factory()
                            ->create([
                                    'payment_status' => 'paid',
                                    'processing_status' => 'queued',
                                    'attempt_count' => 0,
                                ]);

    $job                 = new ProcessAnalysisRequest($analysisRequest->id);
    $growthReportService = app(GrowthReportService::class);
    $executeJob          = fn () => $job->handle($growthReportService);

    expect($executeJob)
        ->toThrow(
            RuntimeException::class,
            'API rate limit'
        );

    $analysisRequest->refresh();
    $lastError = $analysisRequest->last_error;
    expect($lastError)
        ->toBe('API rate limit');
});

it('marks record as failed and deletes it when job fails after max attempts', function (): void {
    $analysisRequest = AnalysisRequest::factory()
                            ->create([
                                'payment_status' => 'paid',
                                'processing_status' => 'processing',
                            ]);

    $job = new ProcessAnalysisRequest($analysisRequest->id);
    $exception = new RuntimeException('Final failure');
    $job->failed($exception);

    $missingAnalysisRequest = AnalysisRequest::find($analysisRequest->id);
    expect($missingAnalysisRequest)
        ->toBeNull();
});
