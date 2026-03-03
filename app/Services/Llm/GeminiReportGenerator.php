<?php

namespace App\Services\Llm;

use App\Ai\Agents\GrowthReportAgent;
use App\Contracts\ReportGenerator;
use Laravel\Ai\Enums\Lab;

/**
 * Report generator using Laravel AI SDK with Gemini (ADR-019).
 * FDR-007.2: adapter only; prompt building and parsing are in FDR-007.3.
 */
class GeminiReportGenerator implements ReportGenerator
{
    public function __construct(
        protected GrowthReportAgent $agent,
        protected int $timeout = 240,
        protected ?string $model = null
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function generateReport(array $payload, string $locale): string
    {
        $prompt = $this->buildMinimalPrompt($payload, $locale);

        $response = $this->agent->prompt(
            $prompt,
            [],
            Lab::Gemini,
            $this->model,
            $this->timeout
        );

        return (string) $response;
    }

    /**
     * Minimal prompt for FDR-007.2; full template in FDR-007.3.
     *
     * @param  array<string, mixed>  $payload
     */
    protected function buildMinimalPrompt(array $payload, string $locale): string
    {
        $username = $payload['tiktok_username'] ?? $payload['username'] ?? '';
        $bio = $payload['bio'] ?? '';
        $niche = $payload['aspiring_niche'] ?? $payload['niche'] ?? '';
        $notes = $payload['notes'] ?? '';
        $v1 = $payload['video_url_1'] ?? $payload['video_1'] ?? '';
        $v2 = $payload['video_url_2'] ?? $payload['video_2'] ?? '';
        $v3 = $payload['video_url_3'] ?? $payload['video_3'] ?? '';

        $lang = match (strtolower($locale)) {
            'es' => 'Spanish',
            'pt' => 'Portuguese',
            default => 'English',
        };

        return "Generate a short TikTok profile growth analysis. Output in {$lang}.\n\n"
            ."Username: {$username}\nBio: {$bio}\nAspiring niche: {$niche}\n"
            ."Video links: 1) {$v1} 2) {$v2} 3) {$v3}\nNotes: {$notes}";
    }
}
