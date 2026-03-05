<?php

it('shows the start growth page with no javascript errors', function () {
    $page = visit('/start-growth');

    $page
        ->assertSee('What you get in your report')
        ->assertSee('Start My Growth')
        ->assertNoSmoke();
});

it('shows validation errors when form is submitted with invalid data', function () {
    $page = visit('/start-growth');
    $page->waitForEvent('networkidle');

    $page
        ->assertSee('What you get in your report')
        ->fill('email', 'invalid-email')
        ->fill('aspiring_niche', 'Lifestyle')
        ->click('@start-growth-submit');

    $page->assertSee('email', ignoreCase: true);
    $page->assertNoSmoke();
});

it('shows form first then copy on mobile viewport', function () {
    $page = visit('/start-growth');
    $page->waitForEvent('networkidle');

    $page
        ->assertSee('What you get in your report')
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
