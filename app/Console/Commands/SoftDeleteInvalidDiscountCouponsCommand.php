<?php

namespace App\Console\Commands;

use App\Models\DiscountCoupon;
use Illuminate\Console\Command;

class SoftDeleteInvalidDiscountCouponsCommand extends Command
{
    protected $signature = 'discount-coupons:soft-delete-invalid';

    protected $description = 'Soft-delete discount coupons that are expired or exhausted';

    public function handle(): int
    {
        $query = DiscountCoupon::query()
            ->where(function ($q): void {
                $q->where(function ($q2): void {
                    $q2->whereNotNull('expires_at')
                        ->where('expires_at', '<=', now());
                })->orWhere(function ($q2): void {
                    $q2->whereNotNull('max_uses')
                        ->whereColumn('times_used', '>=', 'max_uses');
                });
            });

        $count = 0;

        $query->each(function (DiscountCoupon $coupon) use (&$count): void {
            if ($coupon->trashed()) {
                return;
            }
            $coupon->delete();
            $count++;
        });

        $this->info("Soft-deleted {$count} invalid coupon(s).");

        return self::SUCCESS;
    }
}
