<?php

use App\Jobs\ProcessAnalysisRequest;
use App\Models\AnalysisRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('runs without error when analysis request exists and is paid', function (): void {
    $analysisRequest = AnalysisRequest::factory()->create([
        'payment_status' => 'paid',
        'processing_status' => 'queued',
    ]);

    $job = new ProcessAnalysisRequest($analysisRequest->id);
    $job->handle();

    // Stub does not change state; just ensure no exception and handle completes.
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
