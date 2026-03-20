<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Stripe\StripeClient;

uses(RefreshDatabase::class);

it('returns 422 for invalid coupon without calling stripe when keys are set', function (): void {
    config([
            'cashier.key' => 'pk_test_fake',
            'cashier.secret' => 'sk_test_fake',
        ]);

    $message = __('form.coupon_invalid');
    $this->getJson('/start-growth/payment-intent?coupon_code=NOTREAL')
        ->assertStatus(422)
        ->assertJsonFragment(['message' => $message]);
});

it('allows full-price payment intent immediately after invalid coupon (recovery flow)', function (): void {
    config([
            'services.stripe.price_in_cents'    => 3500,
            'services.stripe.currency'          => 'usd',
            'cashier.key'                       => 'pk_test_example',
            'cashier.secret'                    => 'sk_test_example',
        ]);

    $this->getJson('/start-growth/payment-intent?coupon_code=NOTREAL')
        ->assertStatus(422);

    app()->bind(StripeClient::class, function () {
        return new class
        {
            public object $paymentIntents;

            public function __construct()
            {
                $this->paymentIntents = new class
                {
                    public function create(array $payload): object
                    {
                        expect($payload['metadata']['discount_coupon_id'] ?? '')->toBe('');

                        return (object) [
                            'id' => 'pi_recovery_after_invalid',
                            'client_secret' => 'cs_recovery',
                        ];
                    }
                };
            }
        };
    });

    $this->getJson('/start-growth/payment-intent')
        ->assertOk()
        ->assertJson([
                'paymentIntentId' => 'pi_recovery_after_invalid',
                'clientSecret' => 'cs_recovery',
                'amountCents' => 3500,
                'discountPercent' => null,
            ]);
});
