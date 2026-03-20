<?php

namespace Database\Factories;

use App\Models\AnalysisRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AnalysisRequest>
 */
class AnalysisRequestFactory extends Factory
{
    protected $model = AnalysisRequest::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email'                         => fake()->unique()->safeEmail(),
            'tiktok_username'               => fake()->userName(),
            'bio'                           => fake()->paragraph(),
            'aspiring_niche'                => fake()->words(2, true),
            'video_url_1'                   => fake()->url(),
            'video_url_2'                   => fake()->url(),
            'video_url_3'                   => fake()->url(),
            'notes'                         => fake()->optional()->sentence(),
            'locale'                        => fake()->randomElement(['en', 'es', 'pt']),
            'stripe_checkout_session_id'    => null,
            'stripe_payment_intent_id'      => null,
            'payment_status'                => 'pending',
            'processing_status'             => 'waiting_payment_confirmation',
            'attempt_count'                 => 0,
            'last_error'                    => null,
        ];
    }
}
