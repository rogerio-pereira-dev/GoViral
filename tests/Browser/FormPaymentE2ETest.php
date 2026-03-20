<?php

/**
 * Payment uses real Stripe only (no env to bypass). Test cards: docs/Setup/STRIPE_SETUP.md
 * (e.g. 4242 4242 4242 4242 success, 4000 0000 0000 0002 declined, 4000 0000 0000 9995 insufficient funds).
 */
it('loads payment form with card element when Stripe is configured', function () {
    $cashierKey = config('cashier.key');
    $cashierSecret = config('cashier.secret');

    if (blank($cashierKey) || blank($cashierSecret)) {
        $this->markTestSkipped('Stripe test keys (STRIPE_KEY, STRIPE_SECRET) required. See docs/Setup/STRIPE_SETUP.md.');
    }

    $page = visit('/start-growth');
    $page->waitForEvent('networkidle');

    $page->assertSee('What you get in your report')
        ->fill('email', 'e2e@example.com')
        ->fill('tiktok_username', '@e2e')
        ->fill('aspiring_niche', 'Lifestyle')
        ->fill('bio', 'E2E test.')
        ->fill('video_url_1', 'https://example.com/v1')
        ->fill('video_url_2', 'https://example.com/v2')
        ->fill('video_url_3', 'https://example.com/v3');

    $page->assertPresent('#stripe-card-element');
});

it('keeps stripe card element visible after applying an invalid coupon', function () {
    $cashierKey = config('cashier.key');
    $cashierSecret = config('cashier.secret');

    if (blank($cashierKey) || blank($cashierSecret)) {
        $this->markTestSkipped('Stripe test keys (STRIPE_KEY, STRIPE_SECRET) required. See docs/Setup/STRIPE_SETUP.md.');
    }

    $page = visit('/start-growth');
    $page->waitForEvent('networkidle');

    $page->assertPresent('#stripe-card-element');

    $page->fill('input[name="coupon_code"]', 'INVALID_COUPON_XYZ')
        ->click('@start-growth-coupon-apply')
        ->waitForEvent('networkidle');

    $page->assertPresent('@start-growth-coupon-error')
        ->assertSee('This coupon is not valid')
        ->assertPresent('#stripe-card-element')
        ->assertNoSmoke();
});

it('shows payment declined error when card is declined', function () {
    $cashierKey = config('cashier.key');
    $cashierSecret = config('cashier.secret');

    if (blank($cashierKey) || blank($cashierSecret)) {
        $this->markTestSkipped('Stripe test keys required for declined card flow. See docs/Setup/STRIPE_SETUP.md.');
    }

    $this->markTestSkipped('Declined card E2E requires Stripe Elements iframe handling (card 4000000000000002). Implement when frame helpers are available.');
});
