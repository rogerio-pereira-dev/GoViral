<?php

use App\Models\DiscountCoupon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('reports how many invalid coupons were soft deleted', function (): void {
    DiscountCoupon::factory()->expired()->create();
    DiscountCoupon::factory()->exhausted()->create();

    $this->artisan('discount-coupons:soft-delete-invalid')
        ->expectsOutput('Soft-deleted 2 invalid coupon(s).')
        ->assertExitCode(0);
});

it('reports zero when there are no invalid coupons', function (): void {
    DiscountCoupon::factory()->create([
        'expires_at' => null,
        'max_uses' => null,
        'times_used' => 0,
    ]);

    $this->artisan('discount-coupons:soft-delete-invalid')
        ->expectsOutput('Soft-deleted 0 invalid coupon(s).')
        ->assertExitCode(0);
});
