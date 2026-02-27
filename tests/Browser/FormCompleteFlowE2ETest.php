<?php

use App\Models\AnalysisRequest;

it('runs complete flow from landing to form validation, successful submit, database, and thank you', function () {
    $page = visit('/');

    $page
        ->assertSee('Engineered for Viral Growth.')
        ->click('[dusk="landing-start-growth-button"]')
        ->assertSee('What you get in your report')
        ->fill('email', 'flow-e2e@gmail.com')
        ->fill('aspiring_niche', str_repeat('a', 300))
        ->click('[dusk="start-growth-submit"]')
        ->assertSee('must not be greater than 255 characters.')
        ->fill('tiktok_username', '@flowe2e')
        ->fill('aspiring_niche', 'Lifestyle')
        ->fill('bio', 'Creator focused on fitness routines.')
        ->fill('video_url_1', 'https://example.com/video-1')
        ->fill('video_url_2', 'https://example.com/video-2')
        ->fill('video_url_3', 'https://example.com/video-3')
        ->fill('notes', 'Testing complete browser flow.')
        ->click('[dusk="start-growth-submit"]')
        ->assertSee('Your growth report will be sent to your email within 30 minutes.')
        ->assertNoSmoke();

    $this->assertDatabaseHas('analysis_requests', [
        'email' => 'flow-e2e@gmail.com',
        'tiktok_username' => '@flowe2e',
        'bio' => 'Creator focused on fitness routines.',
        'aspiring_niche' => 'Lifestyle',
        'video_url_1' => 'https://example.com/video-1',
        'video_url_2' => 'https://example.com/video-2',
        'video_url_3' => 'https://example.com/video-3',
        'notes' => 'Testing complete browser flow.',
        'locale' => 'en',
        'payment_status' => 'pending',
    ]);

    expect(AnalysisRequest::count())->toBe(1);
});
