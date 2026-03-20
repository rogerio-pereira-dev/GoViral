<?php

use App\Models\DiscountCoupon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

uses(RefreshDatabase::class);

it('soft deletes expired coupons via command', function (): void {
    $todayNow      = now();
    $yesterdayNow  = now()
                        ->subDay();
    $tomorrowNow   = now()
                        ->addDay();
    $yesterday     = $yesterdayNow->toDateString();
    $today         = $todayNow->toDateString();
    $tomorrow      = $tomorrowNow->toDateString();

    $expired = DiscountCoupon::factory()
                    ->create([
                        'expires_at' => $yesterday,
                    ]);
    $activeToday = DiscountCoupon::factory()
                        ->create([
                            'expires_at' => $today,
                        ]);
    $activeTomorrow = DiscountCoupon::factory()
                        ->create([
                            'expires_at' => $tomorrow,
                        ]);

    Artisan::call('discount-coupons:soft-delete-invalid');

    $expiredIsTrashed = $expired->fresh()
                            ->trashed();
    $activeTodayIsTrashed = $activeToday->fresh()
                                ->trashed();
    $activeTomorrowIsTrashed = $activeTomorrow->fresh()
                                    ->trashed();

    expect($expiredIsTrashed)
        ->toBeTrue();
    expect($activeTodayIsTrashed)
        ->toBeFalse();
    expect($activeTomorrowIsTrashed)
        ->toBeFalse();
});

it('soft deletes exhausted coupons via command', function (): void {
    $exhausted = DiscountCoupon::factory()
                    ->exhausted()
                    ->create();
    $active = DiscountCoupon::factory()
                ->create([
                    'max_uses' => 10,
                    'times_used' => 0,
                ]);

    Artisan::call('discount-coupons:soft-delete-invalid');

    $exhaustedIsTrashed = $exhausted->fresh()
                            ->trashed();
    $activeIsTrashed = $active->fresh()
                            ->trashed();

    expect($exhaustedIsTrashed)
        ->toBeTrue();
    expect($activeIsTrashed)
        ->toBeFalse();
});

it('does nothing when there is no expired or exhausted coupon', function (): void {
    $valid = DiscountCoupon::factory()
                ->create([
                    'expires_at' => null,
                    'max_uses' => null,
                    'times_used' => 0,
                ]);

    $exitCode = Artisan::call('discount-coupons:soft-delete-invalid');

    $validIsTrashed = $valid->fresh()
                        ->trashed();

    expect($exitCode)
        ->toBe(0)
        ->and($validIsTrashed)
        ->toBeFalse();
});

it('skips coupons that are already soft deleted', function (): void {
    $expired = DiscountCoupon::factory()
                    ->expired()
                    ->create();
    $expired->delete();

    $exitCode = Artisan::call('discount-coupons:soft-delete-invalid');

    $expiredIsTrashed = $expired->fresh()
                            ->trashed();

    expect($exitCode)
        ->toBe(0)
        ->and($expiredIsTrashed)
        ->toBeTrue();
});

it('keeps analysis_requests reference after coupon soft deleted', function (): void {
    $coupon = DiscountCoupon::factory()
                ->expired()
                ->create();
    $id = $coupon->id;
    $coupon->delete();

    $couponWithTrashed = DiscountCoupon::withTrashed()
                            ->find($id);

    expect($couponWithTrashed)
        ->not
        ->toBeNull();
});
