<?php

use App\Jobs\ProcessAnalysisRequest;

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

it('uses queue name analysis per FDR-005', function (): void {
    $job = new ProcessAnalysisRequest('fake-id');

    expect($job->queue)->toBe('analysis');
});
