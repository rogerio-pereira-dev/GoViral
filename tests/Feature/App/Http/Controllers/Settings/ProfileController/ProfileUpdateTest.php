<?php

use App\Models\User;

test('profile page is displayed', function () {
    $user = User::factory()
                ->create();
    $profileEditRoute = route('profile.edit');

    $response = $this->actingAs($user)
                    ->get($profileEditRoute);

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()
                ->create();
    $profileUpdateRoute = route('profile.update');
    $profileEditRoute = route('profile.edit');

    $this->actingAs($user)
        ->patch($profileUpdateRoute, [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect($profileEditRoute);

    $user->refresh();

    expect($user->name)
        ->toBe('Test User');
    expect($user->email)
        ->toBe('test@example.com');
    expect($user->email_verified_at)
        ->toBeNull();
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()
                ->create();
    $profileUpdateRoute = route('profile.update');
    $profileEditRoute = route('profile.edit');

    $this->actingAs($user)
        ->patch(
                $profileUpdateRoute,
                [
                    'name' => 'Test User',
                    'email' => $user->email,
                ]
            )
        ->assertSessionHasNoErrors()
        ->assertRedirect($profileEditRoute);

    $emailVerifiedAt = $user->refresh()
                            ->email_verified_at;
    expect($emailVerifiedAt)
        ->not
        ->toBeNull();
});

test('user can delete their account', function () {
    $user = User::factory()
                ->create();
    $profileDestroyRoute = route('profile.destroy');
    $homeRoute = route('home');

    $this->actingAs($user)
        ->delete(
                $profileDestroyRoute,
                [
                    'password' => 'password',
                ]
            )
        ->assertSessionHasNoErrors()
        ->assertRedirect($homeRoute);

    $this->assertGuest();

    $userFresh = $user->fresh();
    expect($userFresh)
        ->toBeNull();
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()
                ->create();
    $profileEditRoute = route('profile.edit');
    $profileDestroyRoute = route('profile.destroy');

    $this->actingAs($user)
        ->from($profileEditRoute)
        ->delete(
                $profileDestroyRoute,
                [
                    'password' => 'wrong-password',
                ]
            )
        ->assertSessionHasErrors('password')
        ->assertRedirect($profileEditRoute);

    $userFresh = $user->fresh();
    expect($userFresh)
        ->not
        ->toBeNull();
});
