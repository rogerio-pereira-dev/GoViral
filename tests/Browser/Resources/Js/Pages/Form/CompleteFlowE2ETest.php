<?php

/**
 * Full flow including payment requires STRIPE_KEY and STRIPE_SECRET (test keys)
 * and completing payment with test card 4242 4242 4242 4242 (see STRIPE_SETUP.md).
 */
it('runs complete flow from landing to form, then payment form loads', function () {
    $page = visit('/');
    $page->assertSee('Engineered for Viral Growth.');

    $page = visit('/start-growth');
    $page->waitForEvent('networkidle');

    $page->assertSee('What you get in your report')
        ->fill('email', 'flow-e2e@gmail.com')
        ->fill('tiktok_username', '@flowe2e')
        ->fill('aspiring_niche', 'Lifestyle')
        ->fill('bio', 'Creator focused on fitness routines.')
        ->fill('video_url_1', 'https://example.com/video-1')
        ->fill('video_url_2', 'https://example.com/video-2')
        ->fill('video_url_3', 'https://example.com/video-3')
        ->fill('notes', 'Testing complete browser flow.');

    $cashierKey    = config('cashier.key');
    $cashierSecret = config('cashier.secret');

    if (blank($cashierKey) || blank($cashierSecret)) {
        $this->markTestSkipped('Stripe test keys required for full flow. See docs/Setup/STRIPE_SETUP.md.');
    }

    $page->assertPresent('#stripe-card-element');
});
