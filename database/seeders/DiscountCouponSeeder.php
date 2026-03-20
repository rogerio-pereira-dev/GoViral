<?php

namespace Database\Seeders;

use App\Models\DiscountCoupon;
use Illuminate\Database\Seeder;

class DiscountCouponSeeder extends Seeder
{
    public function run(): void
    {
        // Never expires (no date, no max uses)
        DiscountCoupon::firstOrCreate(
                ['code' => 'NEVER10'],
                [
                    'value' => 10,
                    'expires_at' => null,
                    'max_uses' => null,
                    'times_used' => 0,
                ],
            );

        // Expires by date
        DiscountCoupon::firstOrCreate(
                ['code' => 'DATE20'],
                [
                    'value' => 20,
                    'expires_at' => now()->addDays(30)->toDateString(),
                    'max_uses' => null,
                    'times_used' => 0,
                ],
            );

        // Expires by usage count
        DiscountCoupon::firstOrCreate(
                ['code' => 'USES30'],
                [
                    'value' => 30,
                    'expires_at' => null,
                    'max_uses' => 100,
                    'times_used' => 0,
                ],
            );
    }
}
