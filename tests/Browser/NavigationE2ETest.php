<?php

use App\Models\User;

it('navigates sidebar and settings navigation', function () {
    /** @var \App\Models\User $user */
    $user = User::factory()->create([
        'email' => 'nav-user@example.com',
        'password' => bcrypt('password'),
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user);

    // Start at dashboard
    $page = visit('/core/dashboard');

    $page
        ->assertSee('Dashboard')
        ->assertNoSmoke();

    // Sidebar -> Profile (core settings profile)
    $page
        ->click('@sidebar-profile-link')
        ->assertPathIs('/core/settings/profile')
        ->assertSee('Profile information')
        ->assertNoSmoke();

    // Settings nav -> Password
    $page
        ->click('@settings-nav-password')
        ->assertPathIs('/core/settings/password')
        ->assertSee('Update password')
        ->assertNoSmoke();

    // Two-Factor page reachable
    $page = visit('/core/settings/two-factor');

    $page
        ->assertNoSmoke();
});
