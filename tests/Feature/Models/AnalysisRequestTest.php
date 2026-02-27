<?php

use App\Models\AnalysisRequest;
use Illuminate\Support\Str;

test('analysis request uses uuid as primary key', function () {
    $analysisRequest = AnalysisRequest::factory()
                            ->create();

    expect(Str::isUuid($analysisRequest->id))->toBeTrue();
});

test('analysis request model uses string key type and non-incrementing ids', function () {
    $analysisRequest = new AnalysisRequest;

    expect($analysisRequest->getKeyType())->toBe('string');
    expect($analysisRequest->getIncrementing())->toBeFalse();
});

test('analysis request casts attempt_count to integer', function () {
    $analysisRequest = AnalysisRequest::factory()
                            ->create([
                                'attempt_count' => '7',
                            ]);

    expect($analysisRequest->attempt_count)->toBeInt();
    expect($analysisRequest->attempt_count)->toBe(7);
});

test('analysis request allows mass assignment for expected fields', function () {
    $analysisRequest = AnalysisRequest::factory()
                            ->create([
                                'email' => 'fillable@example.com',
                                'tiktok_username' => 'fillable_user',
                                'bio' => 'Bio with details.',
                                'aspiring_niche' => 'Lifestyle',
                                'video_url_1' => 'https://example.com/video-1',
                                'video_url_2' => 'https://example.com/video-2',
                                'video_url_3' => 'https://example.com/video-3',
                                'notes' => 'Some notes',
                                'locale' => 'pt',
                                'stripe_checkout_session_id' => 'cs_test_123',
                                'stripe_payment_intent_id' => 'pi_test_123',
                                'payment_status' => 'pending',
                                'processing_status' => 'queued',
                                'attempt_count' => 2,
                                'last_error' => 'none',
                            ]);

    expect($analysisRequest->email)->toBe('fillable@example.com');
    expect($analysisRequest->stripe_checkout_session_id)->toBe('cs_test_123');
    expect($analysisRequest->stripe_payment_intent_id)->toBe('pi_test_123');
    expect($analysisRequest->attempt_count)->toBe(2);
});

test('analysis request paid scope returns only paid records', function () {
    $paid = AnalysisRequest::factory()
                ->create([
                    'email' => 'paid@example.com',
                    'payment_status' => 'paid',
                ]);

    AnalysisRequest::factory()
        ->create([
            'email' => 'pending@example.com',
            'payment_status' => 'pending',
        ]);

    $paidRecords = AnalysisRequest::query()
                        ->paid()
                        ->pluck('id');
    $paidRecordCount = $paidRecords->count();
    $firstPaidRecordId = $paidRecords->first();

    expect($paidRecordCount)->toBe(1);
    expect($firstPaidRecordId)->toBe($paid->id);
});

test('analysis request pending payment scope returns only pending records', function () {
    $pending = AnalysisRequest::factory()
                    ->create([
                        'email' => 'pending-scope@example.com',
                        'payment_status' => 'pending',
                    ]);

    AnalysisRequest::factory()
        ->create([
            'email' => 'paid-scope@example.com',
            'payment_status' => 'paid',
        ]);

    $pendingRecords = AnalysisRequest::query()
                        ->pendingPayment()
                        ->pluck('id');
    $pendingRecordCount = $pendingRecords->count();
    $firstPendingRecordId = $pendingRecords->first();

    expect($pendingRecordCount)->toBe(1);
    expect($firstPendingRecordId)->toBe($pending->id);
});

test('analysis request processing status scope filters by status', function () {
    $processing = AnalysisRequest::factory()
                        ->create([
                            'email' => 'processing@example.com',
                            'processing_status' => 'processing',
                        ]);

    AnalysisRequest::factory()
        ->create([
            'email' => 'queued@example.com',
            'processing_status' => 'queued',
        ]);

    $processingRecords = AnalysisRequest::query()
                            ->processingStatus('processing')
                            ->pluck('id');
    $processingRecordCount = $processingRecords->count();
    $firstProcessingRecordId = $processingRecords->first();

    expect($processingRecordCount)->toBe(1);
    expect($firstProcessingRecordId)->toBe($processing->id);
});
