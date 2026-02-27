<?php

namespace App\Http\Requests\Form;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

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
        return [
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255'],
            'tiktok_username' => ['required', 'string', 'max:255'],
            'bio' => ['required', 'string', 'max:5000'],
            'aspiring_niche' => ['required', 'string', 'max:255'],
            'video_url_1' => ['required', 'url', 'max:2048'],
            'video_url_2' => ['required', 'url', 'max:2048'],
            'video_url_3' => ['required', 'url', 'max:2048'],
            'notes' => ['nullable', 'string', 'max:5000'],
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
