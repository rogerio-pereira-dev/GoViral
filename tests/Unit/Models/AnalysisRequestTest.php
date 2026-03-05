<?php

use App\Models\AnalysisRequest;

it('ignores non-fillable attributes on fill', function (): void {
    $analysisRequest = new AnalysisRequest;
    $analysisRequest->fill([
        'email' => 'mass@example.com',
        'aspiring_niche' => 'Lifestyle',
        'payment_status' => 'pending',
        'processing_status' => 'waiting_payment_confirmation',
        'forbidden_attr' => 'must_not_appear',
    ]);

    expect($analysisRequest->getAttributes())->not->toHaveKey('forbidden_attr');
});
