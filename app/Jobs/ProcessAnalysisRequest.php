<?php

namespace App\Jobs;

use App\Contracts\ReportGenerator;
use App\Models\AnalysisRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ProcessAnalysisRequest implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /** ADR-011: max 12 attempts (~1 h with 5 min backoff). */
    public int $tries = 12;

    /** 5-minute delay between retries (seconds). */
    public int $backoff = 300;

    /** Ceiling for LLM call + email delivery (seconds). */
    public int $timeout = 300;

    public function __construct(
        public string $analysisRequestId
    ) {}

    public function handle(ReportGenerator $reportGenerator): void
    {
        $analysisRequest = AnalysisRequest::find($this->analysisRequestId);

        if (! $analysisRequest || $analysisRequest->payment_status !== 'paid') {
            return;
        }

        $analysisRequest->update([
            'processing_status' => 'processing',
            'attempt_count' => $analysisRequest->attempt_count + 1,
        ]);

        try {
            $payload = [
                'tiktok_username' => $analysisRequest->tiktok_username,
                'bio' => $analysisRequest->bio,
                'aspiring_niche' => $analysisRequest->aspiring_niche,
                'video_url_1' => $analysisRequest->video_url_1,
                'video_url_2' => $analysisRequest->video_url_2,
                'video_url_3' => $analysisRequest->video_url_3,
                'notes' => $analysisRequest->notes,
            ];
            $reportGenerator->generateReport($payload, $analysisRequest->locale ?? 'en');

            // FDR-007.3: parse response and build HTML; FDR-008: send email; FDR-005: update status/sent, delete record.
        } catch (Throwable $e) {
            $analysisRequest->update([
                'last_error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
