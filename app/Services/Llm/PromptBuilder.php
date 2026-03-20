<?php

namespace App\Services\Llm;

use Illuminate\Support\Str;

/**
 * Builds the full LLM prompt from docs/LLM Prompt Template.md.
 * FDR-007.3: placeholders USERNAME, BIO, NICHE, VIDEO_1/2/3, NOTES, LANGUAGE.
 * Escapes user input to reduce prompt injection risk.
 */
class PromptBuilder
{
    public function __construct(
        private string $templatePath = ''
    ) {
        if ($this->templatePath === '') {
            $this->templatePath = base_path('docs/LLM Prompt Template.md');
        }
    }

    public function build(array $payload, string $locale): string
    {
        $template     = $this->loadTemplate();
        $replacements = $this->replacements($payload, $locale);

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $template
        );
    }

    private function loadTemplate(): string
    {
        $path = $this->templatePath;

        if (! is_readable($path)) {
            throw new \RuntimeException('LLM Prompt Template not found: docs/LLM Prompt Template.md');
        }

        return (string) file_get_contents($path);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, string>
     */
    private function replacements(array $payload, string $locale): array
    {
        $username = $this->escape($payload['tiktok_username'] ?? $payload['username'] ?? '');
        $bio      = $this->escape($payload['bio'] ?? '');
        $niche    = $this->escape($payload['aspiring_niche'] ?? $payload['niche'] ?? '');
        $v1       = $this->escape($payload['video_url_1'] ?? $payload['video_1'] ?? '');
        $v2       = $this->escape($payload['video_url_2'] ?? $payload['video_2'] ?? '');
        $v3       = $this->escape($payload['video_url_3'] ?? $payload['video_3'] ?? '');
        $notes    = $this->escape($payload['notes'] ?? '');

        $language = match (strtolower($locale)) {
            'es' => 'Spanish',
            'pt' => 'Portuguese',
            default => 'English',
        };

        return [
            '{{USERNAME}}' => $username !== '' ? $username : 'N/A',
            '{{BIO}}' => $bio !== '' ? $bio : 'N/A',
            '{{NICHE}}' => $niche !== '' ? $niche : 'N/A',
            '{{VIDEO_1}}' => $v1 !== '' ? $v1 : 'N/A',
            '{{VIDEO_2}}' => $v2 !== '' ? $v2 : 'N/A',
            '{{VIDEO_3}}' => $v3 !== '' ? $v3 : 'N/A',
            '{{NOTES}}' => $notes !== '' ? $notes : 'N/A',
            '{{LANGUAGE}}' => $language,
        ];
    }

    /**
     * Escape user-provided content to reduce prompt injection risk.
     */
    private function escape(mixed $value): string
    {
        if (! is_string($value)) {
            return '';
        }

        $s = trim(strip_tags($value));
        $s = preg_replace('/\s+/', ' ', $s) ?? $s;
        $s = str_replace(["\r", "\n"], ' ', $s);

        return Str::limit($s, 2000);
    }
}
