<?php

/**
 * Payment uses real Stripe only (no env to bypass). Test cards: docs/Setup/STRIPE_SETUP.md
 * (e.g. 4242 4242 4242 4242 success, 4000 0000 0000 0002 declined, 4000 0000 0000 9995 insufficient funds).
 */
it('loads payment form with card element when Stripe is configured', function () {
    if (blank(config('cashier.key')) || blank(config('cashier.secret'))) {
        $this->markTestSkipped('Stripe test keys (STRIPE_KEY, STRIPE_SECRET) required. See docs/Setup/STRIPE_SETUP.md.');
    }

    $page = visit('/start-growth');
    $page->waitForEvent('networkidle');

    $page
        ->assertSee('What you get in your report')
        ->fill('email', 'e2e@example.com')
        ->fill('tiktok_username', '@e2e')
        ->fill('aspiring_niche', 'Lifestyle')
        ->fill('bio', 'E2E test.')
        ->fill('video_url_1', 'https://example.com/v1')
        ->fill('video_url_2', 'https://example.com/v2')
        ->fill('video_url_3', 'https://example.com/v3');

    $page->assertPresent('#stripe-card-element');
});
