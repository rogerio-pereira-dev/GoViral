<?php

namespace App\Console\Commands;

use App\Models\DiscountCoupon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class SoftDeleteInvalidDiscountCouponsCommand extends Command
{
    protected $signature = 'discount-coupons:soft-delete-invalid';

    protected $description = 'Soft-delete discount coupons that are expired or exhausted';

    public function handle(): int
    {
        $query = DiscountCoupon::query()
            ->where(function (Builder $q): void {
                // Expired by date
                $q->where(function (Builder $q2): void {
                    $q2->whereNotNull('expires_at')
                        ->where('expires_at', '<=', now());
                    // Used times > max_uses
                })->orWhere(function (Builder $q2): void {
                    $q2->whereNotNull('max_uses')
                        ->whereColumn('times_used', '>=', 'max_uses');
                });
            });

        $count = 0;

        // Soft-delete each invalid coupon and keep a counter for observability
        $query->each(function (DiscountCoupon $coupon) use ($count): void {
            $coupon->delete();
            $count++;
        });

        $this->info("Soft-deleted {$count} invalid coupon(s).");

        return self::SUCCESS;
    }
}
