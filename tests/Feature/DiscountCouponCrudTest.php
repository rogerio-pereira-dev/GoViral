<?php

use App\Models\DiscountCoupon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects guests from discount coupons index to login', function (): void {
    $this->get('/core/discount-coupons')->assertRedirect(route('login'));
});

it('allows authenticated user to view index', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/core/discount-coupons')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Core/DiscountCoupons/Index'));
});

it('creates a coupon and redirects to index', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/core/discount-coupons', [
            'code' => 'SAVE20',
            'value' => 20,
            'expiration_type' => 'never',
        ])
        ->assertRedirect('/core/discount-coupons');

    $coupon = DiscountCoupon::where('code', 'SAVE20')->first();
    expect($coupon)->not->toBeNull()
        ->and($coupon->value)->toBe(20)
        ->and($coupon->expires_at)->toBeNull()
        ->and($coupon->max_uses)->toBeNull();
});

it('rejects duplicate active coupon code', function (): void {
    $user = User::factory()->create();
    DiscountCoupon::factory()->create(['code' => 'DUP']);

    $this->actingAs($user)
        ->post('/core/discount-coupons', [
            'code' => 'DUP',
            'value' => 10,
            'expiration_type' => 'never',
        ])
        ->assertSessionHasErrors('code');
});

it('soft deletes coupon on destroy', function (): void {
    $user = User::factory()->create();
    $coupon = DiscountCoupon::factory()->create();

    $this->actingAs($user)
        ->delete("/core/discount-coupons/{$coupon->id}")
        ->assertRedirect('/core/discount-coupons');

    expect(DiscountCoupon::whereKey($coupon->id)->exists())->toBeFalse();
    expect(DiscountCoupon::withTrashed()->whereKey($coupon->id)->exists())->toBeTrue();
});

it('updates coupon', function (): void {
    $user = User::factory()->create();
    $coupon = DiscountCoupon::factory()->create(['code' => 'OLD', 'value' => 5]);

    $this->actingAs($user)
        ->put("/core/discount-coupons/{$coupon->id}", [
            'code' => 'NEWCODE',
            'value' => 15,
            'expiration_type' => 'uses',
            'max_uses_input' => 50,
        ])
        ->assertRedirect('/core/discount-coupons');

    $coupon->refresh();
    expect($coupon->code)->toBe('NEWCODE')
        ->and($coupon->value)->toBe(15)
        ->and($coupon->max_uses)->toBe(50);
});

it('builds edit payload with days expiration from controller helper', function (): void {
    $user = User::factory()->create();
    $coupon = DiscountCoupon::factory()->expiresInDays(10)->create(['value' => 15]);

    $this->actingAs($user)
        ->get("/core/discount-coupons/{$coupon->id}/edit")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Core/DiscountCoupons/Edit')
            ->where('coupon.id', $coupon->id)
            ->where('coupon.value', 15)
            ->where('coupon.expiration_type', 'date')
        );
});

it('builds edit payload with uses expiration from controller helper', function (): void {
    $user = User::factory()->create();
    $coupon = DiscountCoupon::factory()->create([
        'value' => 25,
        'max_uses' => 42,
        'expires_at' => null,
    ]);

    $this->actingAs($user)
        ->get("/core/discount-coupons/{$coupon->id}/edit")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Core/DiscountCoupons/Edit')
            ->where('coupon.id', $coupon->id)
            ->where('coupon.value', 25)
            ->where('coupon.expiration_type', 'uses')
            ->where('coupon.max_uses_input', 42)
        );
});

it('creates coupon with days expiration using store request rules', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/core/discount-coupons', [
            'code' => 'FLOWDAYS',
            'value' => 5,
            'expiration_type' => 'date',
            'expiration_date' => now()->addDays(3)->toDateString(),
        ])
        ->assertRedirect('/core/discount-coupons');

    $coupon = DiscountCoupon::where('code', 'FLOWDAYS')->firstOrFail();

    expect($coupon->value)->toBe(5)
        ->and($coupon->expires_at)->not->toBeNull()
        ->and($coupon->max_uses)->toBeNull()
        ->and($coupon->times_used)->toBe(0);
});

it('creates coupon with uses expiration using store request rules', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/core/discount-coupons', [
            'code' => 'FLOWUSES',
            'value' => 10,
            'expiration_type' => 'uses',
            'max_uses_input' => 7,
        ])
        ->assertRedirect('/core/discount-coupons');

    $coupon = DiscountCoupon::where('code', 'FLOWUSES')->firstOrFail();

    expect($coupon->value)->toBe(10)
        ->and($coupon->expires_at)->toBeNull()
        ->and($coupon->max_uses)->toBe(7)
        ->and($coupon->times_used)->toBe(0);
});

it('updates coupon to days expiration using update request rules', function (): void {
    $user = User::factory()->create();
    $coupon = DiscountCoupon::factory()->create([
        'code' => 'TO-DAYS',
        'value' => 10,
        'max_uses' => 5,
        'expires_at' => null,
    ]);

    $this->actingAs($user)
        ->put("/core/discount-coupons/{$coupon->id}", [
            'code' => 'TODAYS',
            'value' => 15,
            'expiration_type' => 'date',
            'expiration_date' => now()->addDays(4)->toDateString(),
        ])
        ->assertRedirect('/core/discount-coupons');

    $coupon->refresh();

    expect($coupon->code)->toBe('TODAYS')
        ->and($coupon->value)->toBe(15)
        ->and($coupon->max_uses)->toBeNull()
        ->and($coupon->expires_at)->not->toBeNull();
});

it('finds valid coupon by code case-insensitively', function (): void {
    DiscountCoupon::factory()->create(['code' => 'ABC', 'value' => 10]);

    expect(DiscountCoupon::findValidByCode('abc'))->not->toBeNull();
    expect(DiscountCoupon::findValidByCode('invalid'))->toBeNull();
});

it('excludes expired coupons from checkout lookup', function (): void {
    DiscountCoupon::factory()->expired()->create(['code' => 'EXP']);

    expect(DiscountCoupon::findValidByCode('EXP'))->toBeNull();
});

it('excludes exhausted coupons from checkout lookup', function (): void {
    DiscountCoupon::factory()->exhausted()->create(['code' => 'FULL']);

    expect(DiscountCoupon::findValidByCode('FULL'))->toBeNull();
});

it('uppercases code on save via model booted hook', function (): void {
    $coupon = DiscountCoupon::factory()->create(['code' => 'mixedCase10']);

    expect($coupon->fresh()->code)->toBe('MIXEDCASE10');
});
