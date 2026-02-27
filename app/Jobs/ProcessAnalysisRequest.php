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

    public function __construct(
        public string $analysisRequestId
    ) {}

    public function handle(): void
    {
        // Stub: full implementation in FDR-005 (LLM, report, email).
        $analysisRequest = AnalysisRequest::find($this->analysisRequestId);

        if (! $analysisRequest || $analysisRequest->payment_status !== 'paid') {
            return;
        }

        // Placeholder for: set processing_status=processing, call LLM, send email, etc.
    }
}
