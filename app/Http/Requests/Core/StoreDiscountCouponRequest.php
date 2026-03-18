<?php

namespace App\Http\Requests\Core;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDiscountCouponRequest extends FormRequest
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
            'code' => [
                'required',
                'string',
                'max:64',
                'regex:/^[A-Za-z0-9_-]+$/',
                Rule::unique('discount_coupons', 'code')->whereNull('deleted_at'),
            ],
            'value' => ['required', 'integer', 'min:0', 'max:100'],
            'expiration_type' => ['required', 'string', Rule::in(['never', 'date', 'uses'])],
            'expiration_date' => ['required_if:expiration_type,date', 'nullable', 'date'],
            'max_uses_input' => ['required_if:expiration_type,uses', 'nullable', 'integer', 'min:1', 'max:999999'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function couponAttributes(): array
    {
        $type = $this->validated('expiration_type');

        $expiresAt = null;
        $maxUses = null;

        if ($type === 'date') {
            /** @var string|null $date */
            $date = $this->validated('expiration_date');
            $expiresAt = $date;
        }

        if ($type === 'uses') {
            $maxUses = (int) $this->validated('max_uses_input');
        }

        return [
            'code' => strtoupper(trim($this->validated('code'))),
            'value' => (int) $this->validated('value'),
            'expires_at' => $expiresAt,
            'max_uses' => $maxUses,
            'times_used' => 0,
        ];
    }
}
