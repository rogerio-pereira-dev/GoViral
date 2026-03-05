<?php

use App\Services\Llm\PromptBuilder;

beforeEach(function (): void {
    $this->builder = new PromptBuilder;
});

it('builds prompt with all placeholders replaced', function (): void {
    $payload = [
        'tiktok_username' => 'john_creator',
        'bio' => 'My cool bio',
        'aspiring_niche' => 'Fitness',
        'video_url_1' => 'https://tiktok.com/1',
        'video_url_2' => 'https://tiktok.com/2',
        'video_url_3' => 'https://tiktok.com/3',
        'notes' => 'Some notes',
    ];

    $prompt = $this->builder->build($payload, 'en');

    expect($prompt)
        ->toContain('john_creator')
        ->toContain('My cool bio')
        ->toContain('Fitness')
        ->toContain('https://tiktok.com/1')
        ->toContain('https://tiktok.com/2')
        ->toContain('https://tiktok.com/3')
        ->toContain('Some notes')
        ->toContain('English');
});

it('uses N/A for empty optional fields', function (): void {
    $payload = [
        'tiktok_username' => 'user',
        'bio' => 'Bio',
        'aspiring_niche' => 'Niche',
        'video_url_1' => '',
        'video_url_2' => '',
        'video_url_3' => '',
        'notes' => '',
    ];

    $prompt = $this->builder->build($payload, 'en');

    expect($prompt)->toContain('N/A');
});

it('fills LANGUAGE from locale', function (string $locale, string $expectedLanguage): void {
    $payload = [
        'tiktok_username' => 'x',
        'bio' => 'x',
        'aspiring_niche' => 'x',
        'video_url_1' => 'x',
        'video_url_2' => 'x',
        'video_url_3' => 'x',
        'notes' => '',
    ];

    $prompt = $this->builder->build($payload, $locale);

    expect($prompt)->toContain($expectedLanguage);
})->with([
    ['en', 'English'],
    ['es', 'Spanish'],
    ['pt', 'Portuguese'],
]);

it('strips tags and normalizes whitespace in user input', function (): void {
    $payload = [
        'tiktok_username' => "  <script>alert(1)</script>ok  \n\t",
        'bio' => 'Bio',
        'aspiring_niche' => 'Niche',
        'video_url_1' => 'u1',
        'video_url_2' => 'u2',
        'video_url_3' => 'u3',
        'notes' => '',
    ];

    $prompt = $this->builder->build($payload, 'en');

    expect($prompt)
        ->not->toContain('<script>')
        ->not->toContain('</script>')
        ->toContain('ok');
});

it('returns empty string for non-string values when escaping', function (): void {
    $payload = [
        'tiktok_username' => 123,
        'bio' => 'Bio',
        'aspiring_niche' => 'Niche',
        'video_url_1' => 'u1',
        'video_url_2' => 'u2',
        'video_url_3' => 'u3',
        'notes' => null,
    ];

    $prompt = $this->builder->build($payload, 'en');

    expect($prompt)
        ->not->toContain('123')
        ->not->toContain('null');
});

it('throws when template file is not readable', function (): void {
    $builder = new PromptBuilder('/nonexistent/LLM Prompt Template.md');

    $builder->build(
        [
            'tiktok_username' => 'x',
            'bio' => 'x',
            'aspiring_niche' => 'x',
            'video_url_1' => 'x',
            'video_url_2' => 'x',
            'video_url_3' => 'x',
            'notes' => '',
        ],
        'en'
    );
})->throws(RuntimeException::class, 'LLM Prompt Template not found: docs/LLM Prompt Template.md');
