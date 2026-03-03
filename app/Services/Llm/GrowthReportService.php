<?php

namespace App\Services\Llm;

use App\Contracts\ReportGenerator;

/**
 * Orchestrates prompt build, LLM call, and response parsing into HTML.
 * FDR-007.3: full flow; malformed response throws so Job can set last_error.
 */
class GrowthReportService
{
    public function __construct(
        protected ReportGenerator $reportGenerator,
        protected ReportParser $reportParser
    ) {}

    /**
     * Generate report HTML from payload and locale.
     * Uses template prompt, calls LLM, parses and sanitizes to HTML.
     *
     * @param  array<string, mixed>  $payload
     *
     * @throws \Throwable On LLM failure or malformed/empty response
     */
    public function generateReportHtml(array $payload, string $locale): string
    {
        $raw = $this->reportGenerator->generateReport($payload, $locale);

        return $this->reportParser->toHtml($raw);
    }
}
