<?php

use App\Models\AnalysisRequest;

it('completes payment flow with valid payment scenario', function () {
    $page = visit('/start-growth?payment_scenario=valid');

    $page
        ->assertSee('What you get in your report')
        ->fill('email', 'valid-payment@gmail.com')
        ->fill('tiktok_username', '@validpayment')
        ->fill('aspiring_niche', 'Lifestyle')
        ->fill('bio', 'Valid payment browser scenario.')
        ->fill('video_url_1', 'https://example.com/video-1')
        ->fill('video_url_2', 'https://example.com/video-2')
        ->fill('video_url_3', 'https://example.com/video-3')
        ->fill('notes', 'Valid payment scenario')
        ->click('[dusk="start-growth-submit"]')
        ->assertSee('Your growth report will be sent to your email within 30 minutes.')
        ->assertNoSmoke();

    $this->assertDatabaseHas('analysis_requests', [
        'email' => 'valid-payment@gmail.com',
        'payment_status' => 'pending',
        'stripe_payment_intent_id' => 'pi_test_init_valid',
    ]);
});

it('shows declined card message and does not persist analysis request', function () {
    $page = visit('/start-growth?payment_scenario=declined');

    $page
        ->assertSee('What you get in your report')
        ->fill('email', 'declined-payment@gmail.com')
        ->fill('tiktok_username', '@declinedpayment')
        ->fill('aspiring_niche', 'Lifestyle')
        ->fill('bio', 'Declined payment browser scenario.')
        ->fill('video_url_1', 'https://example.com/video-1')
        ->fill('video_url_2', 'https://example.com/video-2')
        ->fill('video_url_3', 'https://example.com/video-3')
        ->fill('notes', 'Declined payment scenario')
        ->click('[dusk="start-growth-submit"]')
        ->assertSee('Your card was declined. Please use another card.')
        ->assertNoSmoke();

    expect(AnalysisRequest::query()->where('email', 'declined-payment@gmail.com')->count())->toBe(0);
});

it('shows insufficient funds message and does not persist analysis request', function () {
    $page = visit('/start-growth?payment_scenario=insufficient_funds');

    $page
        ->assertSee('What you get in your report')
        ->fill('email', 'insufficient-payment@gmail.com')
        ->fill('tiktok_username', '@insufficientpayment')
        ->fill('aspiring_niche', 'Lifestyle')
        ->fill('bio', 'Insufficient funds browser scenario.')
        ->fill('video_url_1', 'https://example.com/video-1')
        ->fill('video_url_2', 'https://example.com/video-2')
        ->fill('video_url_3', 'https://example.com/video-3')
        ->fill('notes', 'Insufficient funds scenario')
        ->click('[dusk="start-growth-submit"]')
        ->assertSee('Your card has insufficient funds.')
        ->assertNoSmoke();

    expect(AnalysisRequest::query()->where('email', 'insufficient-payment@gmail.com')->count())->toBe(0);
});
