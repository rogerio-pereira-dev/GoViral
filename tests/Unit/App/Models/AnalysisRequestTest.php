<?php

use App\Models\AnalysisRequest;

it('ignores non-fillable attributes on fill', function (): void {
    $analysisRequest = new AnalysisRequest;
    $payload         = [
                            'email'             => 'mass@example.com',
                            'aspiring_niche'    => 'Lifestyle',
                            'payment_status'    => 'pending',
                            'processing_status' => 'waiting_payment_confirmation',
                            'forbidden_attr'    => 'must_not_appear',
                        ];

    $analysisRequest->fill($payload);
    $attributes = $analysisRequest->getAttributes();

    expect($attributes)
        ->not
        ->toHaveKey('forbidden_attr');
});
