<?php

it('shows the start growth page with no javascript errors', function () {
    $page = visit('/start-growth');

    $page
        ->assertSee('What you get in your report')
        ->assertSee('Start My Growth')
        ->assertNoSmoke();
});
