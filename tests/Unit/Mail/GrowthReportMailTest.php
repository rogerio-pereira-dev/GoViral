<?php

use App\Mail\GrowthReportMail;
use Illuminate\Support\Facades\App;

beforeEach(function (): void {
    $this->sampleHtml = '<h1>Test Report</h1><p>Content</p>';
});

test('mailable accepts report HTML and locale', function (): void {
    $mailable   = new GrowthReportMail($this->sampleHtml, 'en');
    $reportHtml = $mailable->reportHtml;
    $locale     = $mailable->locale;

    expect($reportHtml)
        ->toBe($this->sampleHtml)
        ->and($locale)
        ->toBe('en');
});

test('mailable envelope subject is translated per locale', function (string $locale, string $expectedSubject): void {
    App::setLocale($locale);

    $mailable   = new GrowthReportMail($this->sampleHtml, $locale);
    $envelope   = $mailable->envelope();
    $subject    = $envelope->subject;

    expect($subject)
        ->toBe($expectedSubject);
})
->with([
    ['en', 'Your GoViral Growth Report'],
    ['es', 'Tu informe de crecimiento GoViral'],
    ['pt', 'Seu relatório de crescimento GoViral'],
]);

test('mailable HTML view contains report content', function (): void {
    $mailable = new GrowthReportMail($this->sampleHtml, 'en');
    $html = $mailable->render();

    expect($html)
        ->toContain('Test Report')
        ->and($html)
        ->toContain('<p>Content</p>');
});

test('mailable has plain text view', function (): void {
    $mailable   = new GrowthReportMail($this->sampleHtml, 'en');
    $content    = $mailable->content();
    $textView   = $content->text;

    expect($textView)
        ->toBe('emails.growth-report-text');
});
