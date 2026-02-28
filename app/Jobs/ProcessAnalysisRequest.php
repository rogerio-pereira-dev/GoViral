<?php

namespace App\Jobs;

use App\Models\AnalysisRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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

    public function handle(): void
    {
        $analysisRequest = AnalysisRequest::find($this->analysisRequestId);

        if (! $analysisRequest || $analysisRequest->payment_status !== 'paid') {
            return;
        }

        // Stub: full implementation in FDR-005 (LLM, report, email).
    }
}
