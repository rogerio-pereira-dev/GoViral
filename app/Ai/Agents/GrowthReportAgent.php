<?php

namespace App\Ai\Agents;

use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;
use Stringable;

/**
 * Agent for TikTok profile growth report generation (ADR-019).
 * Used by GeminiReportGenerator; prompt building is done by the adapter.
 */
#[Provider(Lab::Gemini)]
#[Timeout(240)]
class GrowthReportAgent implements Agent
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     * Full prompt content is passed per request (see LLM Prompt Template).
     */
    public function instructions(): Stringable|string
    {
        return 'You are an expert TikTok growth strategist. Generate a structured, actionable analysis report based on the user prompt. Output in the requested language.';
    }
}
