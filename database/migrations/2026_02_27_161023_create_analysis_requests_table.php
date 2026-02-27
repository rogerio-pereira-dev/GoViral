<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('analysis_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email');
            $table->string('tiktok_username');
            $table->text('bio');
            $table->string('aspiring_niche');
            $table->string('video_url_1');
            $table->string('video_url_2');
            $table->string('video_url_3');
            $table->text('notes')->nullable();
            $table->string('locale', 5);
            $table->string('stripe_checkout_session_id')->nullable()->index();
            $table->string('stripe_payment_intent_id')->nullable();
            $table->string('payment_status')->default('pending');
            $table->string('processing_status')->default('queued');
            $table->unsignedSmallInteger('attempt_count')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analysis_requests');
    }
};
