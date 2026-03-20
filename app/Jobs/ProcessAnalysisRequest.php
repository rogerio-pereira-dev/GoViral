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
        $analysisRequest = AnalysisRequest::find($this->analysisRequestId);

        if (! $analysisRequest || $analysisRequest->payment_status !== 'paid') {
            return;
        }

        $analysisRequest->update([
            'processing_status' => 'processing',
            'attempt_count' => $analysisRequest->attempt_count + 1,
        ]);

        try {
            $locale     = $analysisRequest->locale ?? 'en';
            $reportHtml = $analysisRequest->report_html;

            if ($reportHtml === null) {
                $payload = [
                    'tiktok_username' => $analysisRequest->tiktok_username,
                    'bio' => $analysisRequest->bio,
                    'aspiring_niche' => $analysisRequest->aspiring_niche,
                    'video_url_1' => $analysisRequest->video_url_1,
                    'video_url_2' => $analysisRequest->video_url_2,
                    'video_url_3' => $analysisRequest->video_url_3,
                    'notes' => $analysisRequest->notes,
                ];

                $reportHtml = $reportService->generateReportHtml($payload, $locale);

                $analysisRequest->update([
                    'report_html' => $reportHtml,
                    'last_error' => null,
                ]);
            }

            $mailable = new GrowthReportMail($reportHtml, $locale);
            $mailable->onQueue('emails');

            Mail::to($analysisRequest->email)
                ->queue($mailable);

            $analysisRequest->update([
                'processing_status' => 'sent',
                'sent_at' => now(),
            ]);

            /*
             * After some thinking, i'm not sure if i want to delete the reports
             * Keeping it here
             */
            // $analysisRequest->delete();
        } catch (Throwable $e) {
            $analysisRequest->update([
                'last_error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(?Throwable $exception): void
    {
        $analysisRequest = AnalysisRequest::find($this->analysisRequestId);

        if ($analysisRequest) {
            $analysisRequest->update(['processing_status' => 'failed']);
            $analysisRequest->delete();
        }
    }
}
