<?php

namespace App\Services\Llm;

use App\Ai\Agents\GrowthReportAgent;
use App\Contracts\ReportGenerator;
use Laravel\Ai\Enums\Lab;

/**
 * Report generator using Laravel AI SDK with Gemini (ADR-019).
 * FDR-007.3: full prompt from template via PromptBuilder; returns raw LLM text.
 */
class GeminiReportGenerator implements ReportGenerator
{
    public function __construct(
        protected GrowthReportAgent $agent,
        protected PromptBuilder $promptBuilder,
        protected int $timeout = 240,
        protected ?string $model = null
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function generateReport(array $payload, string $locale): string
    {
        $prompt = $this->promptBuilder->build($payload, $locale);

        $response = $this->agent->prompt(
            $prompt,
            [],
            Lab::Gemini,
            $this->model,
            $this->timeout
        );

        return (string) $response;
    }
}
