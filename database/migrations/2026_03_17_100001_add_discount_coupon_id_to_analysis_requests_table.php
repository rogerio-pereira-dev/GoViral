<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analysis_requests', function (Blueprint $table) {
            $table->foreignUuid('discount_coupon_id')
                ->nullable()
                ->after('sent_at')
                ->constrained('discount_coupons');
        });
    }

    public function down(): void
    {
        Schema::table('analysis_requests', function (Blueprint $table) {
            $table->dropForeign(['discount_coupon_id']);
        });
    }
};
