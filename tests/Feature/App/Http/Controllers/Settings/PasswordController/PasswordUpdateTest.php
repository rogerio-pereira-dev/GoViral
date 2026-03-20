<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('password update page is displayed', function () {
    $user = User::factory()
                ->create();
    $passwordEditRoute = route('user-password.edit');

    $this->actingAs($user)
        ->get($passwordEditRoute)
        ->assertOk();
});

test('password can be updated', function () {
    $user = User::factory()
                ->create();
    $passwordEditRoute = route('user-password.edit');
    $passwordUpdateRoute = route('user-password.update');

    $this->actingAs($user)
        ->from($passwordEditRoute)
        ->put($passwordUpdateRoute, [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect($passwordEditRoute);

    $password = $user->refresh()->password;
    $hashCheck = Hash::check('new-password', $password);
    expect($hashCheck)
        ->toBeTrue();
});

test('correct password must be provided to update password', function () {
    $user = User::factory()
                ->create();
    $passwordEditRoute = route('user-password.edit');
    $passwordUpdateRoute = route('user-password.update');

    $this->actingAs($user)
        ->from($passwordEditRoute)
        ->put($passwordUpdateRoute, [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])
        ->assertSessionHasErrors('current_password')
        ->assertRedirect($passwordEditRoute);
});
