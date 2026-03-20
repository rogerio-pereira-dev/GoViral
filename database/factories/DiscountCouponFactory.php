<?php

namespace Database\Factories;

use App\Models\DiscountCoupon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DiscountCoupon>
 */
class DiscountCouponFactory extends Factory
{
    protected $model = DiscountCoupon::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code'          => strtoupper(fake()->unique()->bothify('SAVE##??')),
            'value'         => fake()->numberBetween(5, 30),
            'expires_at'    => null,
            'max_uses'      => null,
            'times_used'    => 0,
        ];
    }

    public function expiresInDays(int $days): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->addDays($days),
            'max_uses' => null,
        ]);
    }

    public function maxUses(int $max): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => null,
            'max_uses' => $max,
        ]);
    }

    public function exhausted(): static
    {
        return $this->state(fn (array $attributes) => [
            'max_uses' => 1,
            'times_used' => 1,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subDay(),
        ]);
    }
}
