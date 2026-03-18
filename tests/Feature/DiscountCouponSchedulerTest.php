<?php

use App\Models\DiscountCoupon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

uses(RefreshDatabase::class);

it('soft deletes expired coupons via command', function (): void {
    $expired = DiscountCoupon::factory()->expired()->create();
    $active = DiscountCoupon::factory()->create();

    Artisan::call('discount-coupons:soft-delete-invalid');

    expect($expired->fresh()->trashed())->toBeTrue();
    expect($active->fresh()->trashed())->toBeFalse();
});

it('soft deletes exhausted coupons via command', function (): void {
    $exhausted = DiscountCoupon::factory()->exhausted()->create();
    $active = DiscountCoupon::factory()->create(['max_uses' => 10, 'times_used' => 0]);

    Artisan::call('discount-coupons:soft-delete-invalid');

    expect($exhausted->fresh()->trashed())->toBeTrue();
    expect($active->fresh()->trashed())->toBeFalse();
});

it('keeps analysis_requests reference after coupon soft deleted', function (): void {
    $coupon = DiscountCoupon::factory()->expired()->create();
    $id = $coupon->id;
    $coupon->delete();

    expect(DiscountCoupon::withTrashed()->find($id))->not->toBeNull();
});
