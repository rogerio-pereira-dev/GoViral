<?php

use App\Services\Llm\ReportParser;

beforeEach(function (): void {
    $this->parser = new ReportParser;
});

it('converts markdown to HTML', function (): void {
    $raw = "## Profile Score\n\n**Strong:** Good niche.\n\n- Bullet one\n- Bullet two";

    $html = $this->parser->toHtml($raw);

    expect($html)
        ->toContain('<h2>')
        ->toContain('Profile Score')
        ->toContain('<strong>')
        ->toContain('<ul>')
        ->toContain('<li>');
});

it('throws when response is empty', function (): void {
    $this->parser->toHtml('');
})->throws(InvalidArgumentException::class, 'LLM returned empty report');

it('throws when response is only whitespace', function (): void {
    $this->parser->toHtml("  \n\t  ");
})->throws(InvalidArgumentException::class, 'LLM returned empty report');

it('strips dangerous HTML to prevent XSS', function (): void {
    $raw = 'Safe **text** and <script>alert("xss")</script> here.';

    $html = $this->parser->toHtml($raw);

    expect($html)->not->toContain('<script>');
});

it('returns empty sections array when raw content is empty', function (): void {
    $sections = $this->parser->parseSections('');

    expect($sections)->toBe([]);
});

it('returns full content when no section headers are present', function (): void {
    $raw = 'Some plain text without any expected section headers.';

    $sections = $this->parser->parseSections($raw);

    expect($sections)
        ->toHaveKey('full')
        ->and($sections['full'])->toBe($raw);
});

it('parses structured sections when headers are present', function (): void {
    $raw = <<<'TEXT'
1. Executive Summary
Summary content.

2. Profile Score
Score content.

3. Inferred Niche Analysis
Niche content.

4. Username Suggestions
Username content.

5. Optimized Bio
Bio content.

6. Profile Optimization Suggestions
Optimization content.

7. Content Ideas
Content ideas.

8. Viralization Tips
Tips content.

9. 30-Day Action Plan
Plan content.
TEXT;

    $sections = $this->parser->parseSections($raw);

    expect($sections)
        ->toHaveKey('executive_summary')
        ->and($sections['executive_summary'])->toContain('Summary content.')
        ->and($sections['profile_score'])->toContain('Score content.')
        ->and($sections['inferred_niche'])->toContain('Niche content.')
        ->and($sections['username_suggestions'])->toContain('Username content.')
        ->and($sections['optimized_bio'])->toContain('Bio content.')
        ->and($sections['profile_optimization'])->toContain('Optimization content.')
        ->and($sections['content_ideas'])->toContain('Content ideas.')
        ->and($sections['viralization_tips'])->toContain('Tips content.')
        ->and($sections['action_plan'])->toContain('Plan content.');
});
