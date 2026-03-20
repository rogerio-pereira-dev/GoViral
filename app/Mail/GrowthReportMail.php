<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GrowthReportMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $reportHtml,
        string $locale
    ) {
        $this->locale = $locale;
    }

    public function envelope(): Envelope
    {
        $previousLocale = app()->getLocale();
        app()->setLocale($this->locale);

        $subject  = (string) __('report_mail.subject');
        $envelope = new Envelope(
            subject: $subject,
        );

        app()->setLocale($previousLocale);

        return $envelope;
    }

    public function content(): Content
    {
        $previousLocale = app()->getLocale();
        app()->setLocale($this->locale);

        $with = [
            'locale' => $this->locale,
            'subject' => (string) __('report_mail.subject'),
            'intro_heading' => (string) __('report_mail.intro_heading'),
            'intro_body' => (string) __('report_mail.intro_body'),
            'plain_intro' => (string) __('report_mail.plain_intro'),
        ];

        app()->setLocale($previousLocale);

        return new Content(
            view: 'emails.growth-report',
            text: 'emails.growth-report-text',
            with: $with,
        );
    }
}
