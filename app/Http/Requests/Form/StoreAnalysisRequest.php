<?php

namespace App\Http\Requests\Form;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use RyanChandler\LaravelCloudflareTurnstile\Rules\Turnstile as TurnstileRule;

class StoreAnalysisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $turnstileRules = $this->turnstileIsConfigured()
            ? ['required', 'string', new TurnstileRule]
            : ['nullable', 'string'];

        return [
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255'],
            'tiktok_username' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:5000'],
            'aspiring_niche' => ['required', 'string', 'max:255'],
            'video_url_1' => ['nullable', 'url', 'max:2048'],
            'video_url_2' => ['nullable', 'url', 'max:2048'],
            'video_url_3' => ['nullable', 'url', 'max:2048'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'payment_intent_id' => ['required', 'string', 'max:255'],
            'cf-turnstile-response' => $turnstileRules,
        ];
    }

    private function turnstileIsConfigured(): bool
    {
        $secret = config('services.turnstile.secret');

        return is_string($secret) && ! blank($secret);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => __('form.validation.required'),
            'string' => __('form.validation.string'),
            'email' => __('form.validation.email'),
            'url' => __('form.validation.url'),
            'max' => __('form.validation.max'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'email' => __('form.email_label'),
            'tiktok_username' => __('form.tiktok_username_label'),
            'bio' => __('form.bio_label'),
            'aspiring_niche' => __('form.aspiring_niche_label'),
            'video_url_1' => __('form.video_url_1_label'),
            'video_url_2' => __('form.video_url_2_label'),
            'video_url_3' => __('form.video_url_3_label'),
            'notes' => __('form.notes_label'),
            'payment_intent_id' => __('form.payment_card_label'),
            'cf-turnstile-response' => __('form.turnstile_label'),
        ];
    }

    protected function prepareForValidation(): void
    {
        // Sanitize before validation to reduce XSS/injection risk (ADR-017).
        $this->merge([
            'email' => $this->sanitizeInput($this->input('email')),
            'tiktok_username' => $this->sanitizeInput($this->input('tiktok_username')),
            'bio' => $this->sanitizeInput($this->input('bio')),
            'aspiring_niche' => $this->sanitizeInput($this->input('aspiring_niche')),
            'video_url_1' => $this->sanitizeInput($this->input('video_url_1')),
            'video_url_2' => $this->sanitizeInput($this->input('video_url_2')),
            'video_url_3' => $this->sanitizeInput($this->input('video_url_3')),
            'notes' => $this->sanitizeInput($this->input('notes')),
            'payment_intent_id' => $this->sanitizeInput($this->input('payment_intent_id')),
        ]);
    }

    private function sanitizeInput(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        return trim(strip_tags($value));
    }
}
