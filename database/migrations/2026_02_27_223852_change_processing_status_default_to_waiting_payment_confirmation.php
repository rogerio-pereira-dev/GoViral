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
        Schema::table('analysis_requests', function (Blueprint $table) {
            $table->string('processing_status')->default('waiting_payment_confirmation')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('analysis_requests', function (Blueprint $table) {
            $table->string('processing_status')->default('queued')->change();
        });
    }
};
