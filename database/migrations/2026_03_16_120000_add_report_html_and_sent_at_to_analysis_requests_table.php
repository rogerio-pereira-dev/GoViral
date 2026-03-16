<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations. FDR-011: persist report before email; ADR-020 case studies.
     */
    public function up(): void
    {
        Schema::table('analysis_requests', function (Blueprint $table) {
            $table->longText('report_html')->nullable()->after('last_error');
            $table->timestamp('sent_at')->nullable()->after('report_html');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('analysis_requests', function (Blueprint $table) {
            $table->dropColumn(['report_html', 'sent_at']);
        });
    }
};
