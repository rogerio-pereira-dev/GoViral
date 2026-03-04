<?php

namespace App\Jobs;

use App\Mail\GrowthReportMail;
use App\Models\AnalysisRequest;
use App\Services\Llm\GrowthReportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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

    /** Ceiling for LLM call + queue email (seconds). */
    public int $timeout = 300;

    public function __construct(
        public string $analysisRequestId
    ) {
        $this->onQueue('analysis');
    }

    public function handle(GrowthReportService $reportService): void
    {
        Log::error('ProcessAnalysisRequest step 1 started', ['id' => $this->analysisRequestId]);

        $analysisRequest = AnalysisRequest::find($this->analysisRequestId);

        if (! $analysisRequest || $analysisRequest->payment_status !== 'paid') {
            return;
        }

        $analysisRequest->update([
            'processing_status' => 'processing',
            'attempt_count' => $analysisRequest->attempt_count + 1,
        ]);

        $payload = [
            'tiktok_username' => $analysisRequest->tiktok_username,
            'bio' => $analysisRequest->bio,
            'aspiring_niche' => $analysisRequest->aspiring_niche,
            'video_url_1' => $analysisRequest->video_url_1,
            'video_url_2' => $analysisRequest->video_url_2,
            'video_url_3' => $analysisRequest->video_url_3,
            'notes' => $analysisRequest->notes,
        ];
        $locale = $analysisRequest->locale ?? 'en';

        try {
            $reportHtml = $reportService->generateReportHtml($payload, $locale);
        } catch (Throwable $e) {
            Log::error('ProcessAnalysisRequest step 2 failed (report)', ['error' => $e->getMessage()]);
            $analysisRequest->update(['last_error' => $e->getMessage()]);

            throw $e;
        }

        Log::error('ProcessAnalysisRequest step 2 done (report)', ['id' => $this->analysisRequestId]);

        try {
            Mail::to($analysisRequest->email)
                ->queue(
                    (new GrowthReportMail($reportHtml, $locale))->onQueue('emails')
                );
        } catch (Throwable $e) {
            Log::error('ProcessAnalysisRequest step 3 failed (queue email)', ['error' => $e->getMessage()]);
            $analysisRequest->update(['last_error' => $e->getMessage()]);

            throw $e;
        }

        $analysisRequest->update(['processing_status' => 'sent']);

        Log::error('ProcessAnalysisRequest step 3 done (email queued)', ['id' => $this->analysisRequestId]);

        /*
         * After some thinking, i'm not sure if i want to delete the reports
         * Keeping it here
         */
        // $analysisRequest->delete();
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('ProcessAnalysisRequest job failed', ['error' => $exception?->getMessage()]);

        $analysisRequest = AnalysisRequest::find($this->analysisRequestId);

        if ($analysisRequest) {
            $analysisRequest->update(['processing_status' => 'failed']);
            $analysisRequest->delete();
        }
    }
}
