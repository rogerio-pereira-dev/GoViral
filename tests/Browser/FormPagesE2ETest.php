<?php

it('shows the start growth page with no javascript errors', function () {
    $page = visit('/start-growth');

    $page->assertSee('What you get in your report')
        ->assertSee('Start My Growth')
        ->assertNoSmoke();
});

it('shows validation failed message when required fields are missing and does not show payment error', function () {
    $this->markTestSkipped(
            'Submit button stays disabled until payment intent loads or fails; client-side validation runs on click. Feature tests cover 422 for missing aspiring_niche and validation_failed_message translation.'
        );
});

it('shows form first then copy on mobile viewport', function () {
    $page = visit('/start-growth');
    $page->waitForEvent('networkidle');

    $page->assertSee('What you get in your report')
        ->resize(375, 667);

    $page->assertScript(
        "function() {
            const form = document.querySelector('.form-panel');
            const copy = document.querySelector('.copy-panel');
            if (!form || !copy) return false;
            const formTop = form.getBoundingClientRect().top;
            const copyTop = copy.getBoundingClientRect().top;
            return formTop < copyTop;
        }",
        true
    );

    $page->assertNoSmoke();
});
