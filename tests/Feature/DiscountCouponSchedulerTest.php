<?php

use App\Models\DiscountCoupon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

uses(RefreshDatabase::class);

it('soft deletes expired coupons via command', function (): void {
    $expired = DiscountCoupon::factory()->create([
        'expires_at' => now()->subDay()->toDateString(),
    ]);
    $activeToday = DiscountCoupon::factory()->create([
        'expires_at' => now()->toDateString(),
    ]);
    $activeTomorrow = DiscountCoupon::factory()->create([
        'expires_at' => now()->addDay()->toDateString(),
    ]);

    Artisan::call('discount-coupons:soft-delete-invalid');

    expect($expired->fresh()->trashed())->toBeTrue();
    expect($activeToday->fresh()->trashed())->toBeFalse();
    expect($activeTomorrow->fresh()->trashed())->toBeFalse();
});

it('soft deletes exhausted coupons via command', function (): void {
    $exhausted = DiscountCoupon::factory()->exhausted()->create();
    $active    = DiscountCoupon::factory()->create(['max_uses' => 10, 'times_used' => 0]);

    Artisan::call('discount-coupons:soft-delete-invalid');

    expect($exhausted->fresh()->trashed())->toBeTrue();
    expect($active->fresh()->trashed())->toBeFalse();
});

it('does nothing when there is no expired or exhausted coupon', function (): void {
    $valid = DiscountCoupon::factory()->create([
        'expires_at' => null,
        'max_uses' => null,
        'times_used' => 0,
    ]);

    $exitCode = Artisan::call('discount-coupons:soft-delete-invalid');

    expect($exitCode)->toBe(0)
        ->and($valid->fresh()->trashed())->toBeFalse();
});

it('skips coupons that are already soft deleted', function (): void {
    $expired = DiscountCoupon::factory()->expired()->create();
    $expired->delete();

    $exitCode = Artisan::call('discount-coupons:soft-delete-invalid');

    expect($exitCode)->toBe(0)
        ->and($expired->fresh()->trashed())->toBeTrue();
});

it('keeps analysis_requests reference after coupon soft deleted', function (): void {
    $coupon = DiscountCoupon::factory()->expired()->create();
    $id     = $coupon->id;
    $coupon->delete();

    expect(DiscountCoupon::withTrashed()->find($id))->not->toBeNull();
});
