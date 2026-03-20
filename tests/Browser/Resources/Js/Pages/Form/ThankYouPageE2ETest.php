<?php

it('redirects to home when visiting thank-you without completing flow', function () {
    $page = visit('/thank-you');

    $page->assertPathIs('/')
        ->assertNoSmoke();
});
