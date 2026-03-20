<?php

use App\Models\AnalysisRequest;
use App\Models\DiscountCoupon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('relates analysis requests to discount coupon', function (): void {
    $coupon = DiscountCoupon::factory()->create();

    $request = AnalysisRequest::factory()->create([
        'discount_coupon_id' => $coupon->id,
    ]);

    $related = $coupon->analysisRequests;

    expect($related)->toHaveCount(1)
        ->and($related->first()->id)->toBe($request->id);
});

it('computes discountedAmountCents with percentage and minimum amount', function (): void {
    // No discount
    expect(DiscountCoupon::discountedAmountCents(10000, 0))->toBe(10000);

    // 20% discount
    expect(DiscountCoupon::discountedAmountCents(10000, 20))->toBe(8000);

    // 100% discount should still respect the 50-cent minimum
    expect(DiscountCoupon::discountedAmountCents(4000, 100))->toBe(50);
});

it('findValidByCode returns null for blank code', function (): void {
    expect(DiscountCoupon::findValidByCode(''))->toBeNull();
    expect(DiscountCoupon::findValidByCode('   '))->toBeNull();
});

it('treats expires_at as date-only and keeps coupons valid through expiration date', function (): void {
    $today = now()->toDateString();

    $couponToday = DiscountCoupon::factory()->create([
        'expires_at' => $today,
        'max_uses' => null,
        'times_used' => 0,
    ]);

    $couponTomorrow = DiscountCoupon::factory()->create([
        'expires_at' => now()->addDay()->toDateString(),
        'max_uses' => null,
        'times_used' => 0,
    ]);

    $couponYesterday = DiscountCoupon::factory()->create([
        'expires_at' => now()->subDay()->toDateString(),
        'max_uses' => null,
        'times_used' => 0,
    ]);

    $checkoutIncludesToday = DiscountCoupon::validForCheckout()
        ->whereKey($couponToday->id)
        ->exists();

    $checkoutIncludesTomorrow = DiscountCoupon::validForCheckout()
        ->whereKey($couponTomorrow->id)
        ->exists();

    $checkoutIncludesYesterday = DiscountCoupon::validForCheckout()
        ->whereKey($couponYesterday->id)
        ->exists();

    expect($checkoutIncludesToday)->toBeTrue();
    expect($checkoutIncludesTomorrow)->toBeTrue();
    expect($checkoutIncludesYesterday)->toBeFalse();
});
