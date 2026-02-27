<?php

use Inertia\Testing\AssertableInertia as Assert;

it('renders the start growth form page with current locale', function () {
    $response = $this
        ->withSession(['locale' => 'pt'])
        ->get(route('form.index'));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Form/StartGrowth')
            ->where('locale', 'pt')
        );
});
