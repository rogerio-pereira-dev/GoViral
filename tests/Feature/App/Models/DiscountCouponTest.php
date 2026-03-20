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
    $relatedFirst = $related->first();
    $relatedFirstId = $relatedFirst->id;

    expect($related)->toHaveCount(1)
        ->and($relatedFirstId)
        ->toBe($request->id);
});

it('computes discountedAmountCents with percentage and minimum amount', function (): void {
    $amountWithoutDiscount = DiscountCoupon::discountedAmountCents(10000, 0);

    // No discount
    expect($amountWithoutDiscount)->toBe(10000);

    $amountWithTwentyPercentDiscount = DiscountCoupon::discountedAmountCents(10000, 20);
    // 20% discount
    expect($amountWithTwentyPercentDiscount)->toBe(8000);

    $amountWithOneHundredPercentDiscount = DiscountCoupon::discountedAmountCents(4000, 100);
    // 100% discount should still respect the 50-cent minimum
    expect($amountWithOneHundredPercentDiscount)->toBe(50);
});

it('findValidByCode returns null for blank code', function (): void {
    $couponWithEmptyCode = DiscountCoupon::findValidByCode('');
    $couponWithBlankCode = DiscountCoupon::findValidByCode('   ');

    expect($couponWithEmptyCode)->toBeNull();
    expect($couponWithBlankCode)->toBeNull();
});

it('treats expires_at as date-only and keeps coupons valid through expiration date', function (): void {
    $today = now()->toDateString();

    $couponToday = DiscountCoupon::factory()->create([
        'expires_at' => $today,
        'max_uses' => null,
        'times_used' => 0,
    ]);

    $tomorrow = now()
                    ->addDay()
                    ->toDateString();
    $couponTomorrow = DiscountCoupon::factory()->create([
        'expires_at' => $tomorrow,
        'max_uses' => null,
        'times_used' => 0,
    ]);

    $yesterday = now()
                    ->subDay()
                    ->toDateString();
    $couponYesterday = DiscountCoupon::factory()->create([
        'expires_at' => $yesterday,
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
