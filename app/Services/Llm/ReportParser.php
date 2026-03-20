<?php

namespace App\Services\Llm;

use Illuminate\Support\Str;

/**
 * Parses raw LLM report text and converts to safe HTML.
 * FDR-007.3: section structure per template; markdown→HTML with XSS sanitization.
 */
class ReportParser
{
    /**
     * Section headers in the order defined by the template (for optional splitting).
     */
    private const SECTION_HEADERS = [
        '1. Executive Summary',
        '2. Profile Score',
        '3. Inferred Niche Analysis',
        '4. Username Suggestions',
        '5. Optimized Bio',
        '6. Profile Optimization Suggestions',
        '7. Content Ideas',
        '8. Viralization Tips',
        '9. 30-Day Action Plan',
    ];

    /**
     * Allowed HTML tags for report body (no script, iframe, form, etc.).
     */
    private const ALLOWED_HTML_TAGS = '<p><br><h1><h2><h3><h4><ul><ol><li><strong><em><b><i><a>';

    /**
     * Parse raw LLM response and return safe HTML for the report body.
     *
     * @throws \InvalidArgumentException When response is empty or invalid
     */
    public function toHtml(string $raw): string
    {
        $trimmed = trim($raw);

        if ($trimmed === '') {
            throw new \InvalidArgumentException('LLM returned empty report.');
        }

        $html = Str::markdown($trimmed, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        return strip_tags($html, self::ALLOWED_HTML_TAGS);
    }

    /**
     * Parse raw response into structured sections (for optional use).
     * Returns associative array with section keys; content is raw text.
     *
     * @return array<string, string>
     */
    public function parseSections(string $raw): array
    {
        $trimmed = trim($raw);

        if ($trimmed === '') {
            return [];
        }

        $sections = [];
        $keys     = [
            'executive_summary',
            'profile_score',
            'inferred_niche',
            'username_suggestions',
            'optimized_bio',
            'profile_optimization',
            'content_ideas',
            'viralization_tips',
            'action_plan',
        ];

        $pattern = '/\s*'.preg_quote(self::SECTION_HEADERS[0], '/').'\s*/';
        $parts   = preg_split($pattern, $trimmed, 2);

        if (count($parts) < 2) {
            $sections['full'] = $trimmed;

            return $sections;
        }

        $remaining = $trimmed;
        foreach (self::SECTION_HEADERS as $i => $header) {
            $esc        = preg_quote($header, '/');
            $nextHeader = self::SECTION_HEADERS[$i + 1] ?? null;
            $key        = $keys[$i] ?? 'section_'.$i;

            if ($nextHeader !== null) {
                $pattern = '/\s*'.$esc.'\s*(.*?)\s*'.preg_quote($nextHeader, '/').'/s';
                if (preg_match($pattern, $remaining, $m)) {
                    $sections[$key] = trim($m[1]);
                }
            } else {
                if (preg_match('/\s*'.$esc.'\s*(.*)/s', $remaining, $m)) {
                    $sections[$key] = trim($m[1]);
                }
            }
        }

        return $sections;
    }
}
