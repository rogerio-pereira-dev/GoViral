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

    $coupon = DiscountCoupon::query()->where('code', 'SAVE20')->first();
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

    expect(DiscountCoupon::query()->whereKey($coupon->id)->exists())->toBeFalse();
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
