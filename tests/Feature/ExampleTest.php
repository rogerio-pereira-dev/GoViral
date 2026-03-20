<?php

test('returns a successful response', function () {
    $homeRoute = route('home');
    $response = $this->get($homeRoute);

    $response->assertOk();
});
