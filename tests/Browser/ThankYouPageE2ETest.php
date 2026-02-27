<?php

it('shows the thank you page with no javascript errors', function () {
    $page = visit('/thank-you');

    $page
        ->assertSee('Your growth report will be sent to your email within 30 minutes.')
        ->assertNoSmoke();
});
