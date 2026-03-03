<?php

namespace App\Contracts;

interface ReportGenerator
{
    /**
     * Generate report content from the given payload and locale.
     * Returns raw LLM response text. Parsing is done by the caller (FDR-007.3).
     *
     * @param  array<string, mixed>  $payload  Profile data (e.g. username, bio, niche, video_url_1..3, notes)
     * @return string Raw report text from the LLM
     *
     * @throws \Throwable On timeout, API errors (5xx, rate limit), or provider failures
     */
    public function generateReport(array $payload, string $locale): string;
}
