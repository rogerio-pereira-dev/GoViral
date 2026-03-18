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
        $coupons = DiscountCoupon::invalidForUse()
                        ->get();

        $count = 0;

        foreach ($coupons as $coupon) {
            $coupon->delete();
            $count++;
        }

        $this->info("Soft-deleted {$count} invalid coupon(s).");

        return self::SUCCESS;
    }
}
