<?php

use App\Models\User;

it('shows discount coupons index when authenticated', function (): void {
    $user = User::factory()->create([
        'email' => 'coupon-admin@example.com',
        'password' => bcrypt('password'),
    ]);

    $page = visit('/login');
    $page
        ->fill('email', $user->email)
        ->fill('password', 'password')
        ->click('@login-button')
        ->assertSee('Dashboard');

    $page = visit('/core/discount-coupons');
    $page
        ->assertSee('Discount coupons')
        ->assertPresent('[data-test="discount-coupons-table"]')
        ->assertPresent('[data-test="discount-coupons-create-link"]')
        ->assertNoSmoke();
});

it('reaches discount coupons via sidebar link', function (): void {
    $user = User::factory()->create([
        'email' => 'coupon-nav@example.com',
        'password' => bcrypt('password'),
    ]);

    $page = visit('/login');
    $page
        ->fill('email', $user->email)
        ->fill('password', 'password')
        ->click('@login-button');

    $page = visit('/core/dashboard');
    $page->click('@sidebar-discount-coupons-link')
        ->assertPathIs('/core/discount-coupons')
        ->assertSee('Discount coupons')
        ->assertNoSmoke();
});

it('creates coupon and redirects to index', function (): void {
    $user = User::factory()->create([
        'email' => 'coupon-create@example.com',
        'password' => bcrypt('password'),
    ]);

    $page = visit('/login');
    $page
        ->fill('email', $user->email)
        ->fill('password', 'password')
        ->click('@login-button');

    $page = visit('/core/discount-coupons/create');
    $page
        ->assertPresent('[data-test="discount-coupon-create-form"]')
        ->fill('#discount-coupon-code', 'E2E10')
        ->fill('#discount-coupon-value', '10')
        ->click('@discount-coupon-expiration-type')
        ->click('div[role="option"]:has-text("After X uses")') // open menu and close without changing, default remains "Never expires"
        ->click('@discount-coupon-submit')
        ->assertPathIs('/core/discount-coupons')
        ->assertSee('E2E10')
        ->assertNoSmoke();
});

it('edits coupon and returns to index', function (): void {
    $user = User::factory()->create([
        'email' => 'coupon-edit@example.com',
        'password' => bcrypt('password'),
    ]);

    $coupon = \App\Models\DiscountCoupon::factory()->create([
        'code' => 'EDITME',
        'value' => 5,
    ]);

    $page = visit('/login');
    $page
        ->fill('email', $user->email)
        ->fill('password', 'password')
        ->click('@login-button');

    $page = visit("/core/discount-coupons/{$coupon->id}/edit");
    $page
        ->assertPresent('[data-test="discount-coupon-edit-form"]')
        ->fill('#discount-coupon-value', '25')
        ->click('@discount-coupon-submit')
        ->assertPathIs('/core/discount-coupons')
        ->assertNoSmoke();

    expect($coupon->fresh()->value)->toBe(25);
});

it('opens delete dialog and cancels without removing row', function (): void {
    $user = User::factory()->create([
        'email' => 'coupon-del@example.com',
        'password' => bcrypt('password'),
    ]);

    $coupon = \App\Models\DiscountCoupon::factory()->create(['code' => 'KEEPME']);

    $page = visit('/login');
    $page
        ->fill('email', $user->email)
        ->fill('password', 'password')
        ->click('@login-button');

    $page = visit('/core/discount-coupons');
    $page->click("@discount-coupon-delete-{$coupon->id}");
    $page
        ->assertPresent('[data-test="discount-coupon-delete-dialog"]')
        ->click('@discount-coupon-delete-cancel');

    expect(\App\Models\DiscountCoupon::query()->whereKey($coupon->id)->exists())->toBeTrue();
});

it('confirms delete and removes coupon from list', function (): void {
    $user = User::factory()->create([
        'email' => 'coupon-del2@example.com',
        'password' => bcrypt('password'),
    ]);

    $coupon = \App\Models\DiscountCoupon::factory()->create(['code' => 'REMOVEME']);

    $page = visit('/login');
    $page
        ->fill('email', $user->email)
        ->fill('password', 'password')
        ->click('@login-button');

    $page = visit('/core/discount-coupons');
    $page->click("@discount-coupon-delete-{$coupon->id}");
    $page
        ->click('@discount-coupon-delete-confirm')
        ->assertPathIs('/core/discount-coupons');

    expect(\App\Models\DiscountCoupon::query()->whereKey($coupon->id)->exists())->toBeFalse();
});
