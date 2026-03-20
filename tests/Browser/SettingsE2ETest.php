<?php

use App\Models\User;

it('allows updating profile information via the settings page', function () {
    $user = User::factory()->create([
        'name' => 'Original Name',
        'email' => 'settings-profile@example.com',
    ]);

    $this->actingAs($user);

    $page = visit('/core/settings/profile');

    $page->assertSee('Profile information')
        ->assertNoSmoke()
        ->fill('name', 'Updated Name')
        ->click('@update-profile-button')
        ->assertSee('Saved.')
        ->assertNoSmoke();
});

it('allows updating the password via the settings page', function () {
    $user = User::factory()->create([
        'email' => 'settings-password@example.com',
        'password' => bcrypt('current-password'),
    ]);

    $this->actingAs($user);

    $page = visit('/core/settings/password');

    $page->assertSee('Update password')
        ->assertNoSmoke()
        ->fill('current_password', 'current-password')
        ->fill('password', 'new-password-456')
        ->fill('password_confirmation', 'new-password-456')
        ->click('@update-password-button')
        ->assertSee('Saved.')
        ->assertNoSmoke();
});

it('shows the two-factor authentication settings page', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $page = visit('/core/settings/two-factor');

    $page->assertNoSmoke();
});

it('shows the appearance settings page', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $page = visit('/core/settings/appearance');

    $page->assertSee('Appearance settings')
        ->assertNoSmoke();
});
