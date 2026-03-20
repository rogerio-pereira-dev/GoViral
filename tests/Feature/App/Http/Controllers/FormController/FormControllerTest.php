<?php

use App\Models\AnalysisRequest;
use App\Models\DiscountCoupon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use RyanChandler\LaravelCloudflareTurnstile\Facades\Turnstile;
use Stripe\StripeClient;

uses(RefreshDatabase::class);

it('renders the start growth form page with current locale', function () {
    $formIndexRoute = route('form.index');

    $this->withSession([
                'locale' => 'pt'
            ])
        ->get($formIndexRoute)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Form/StartGrowth')
                                                ->where('locale', 'pt')
            );
});

it('stores a new analysis request and returns checkout payload', function () {
    $formStoreRoute = route('form.store');
    $formThankYouRoute = route('form.thank-you');

    config([
            'services.turnstile.secret' => 'test-secret',
            'services.stripe.price_in_cents' => 3500,
        ]);
    Turnstile::fake();
    bindStripeClientForFormStore(
            'pi_test_init', 
            3500, 
            [
                'goviral_base_cents' => '3500',
                'discount_coupon_id' => '',
            ]
        );

    $payload                          = validFormPayload();
    $payload['cf-turnstile-response'] = Turnstile::dummy();

    $this->withSession([
                'locale' => 'es'
            ])
        ->post($formStoreRoute, $payload)
        ->assertOk()
        ->assertJsonStructure([
                'analysisRequestId',
                'thankYouUrl',
            ])
        ->assertJson([
                'thankYouUrl' => $formThankYouRoute,
            ]);

    $this->assertDatabaseHas(
            'analysis_requests', 
            [
                'email'                 => 'creator@gmail.com',
                'tiktok_username'       => '@creator',
                'bio'                   => 'Bio content for analysis.',
                'aspiring_niche'        => 'Lifestyle',
                'video_url_1'           => 'https://example.com/video-1',
                'video_url_2'           => 'https://example.com/video-2',
                'video_url_3'           => 'https://example.com/video-3',
                'notes'                 => 'Optional notes here',
                'locale'                => 'es',
                'payment_status'        => 'pending',
                'processing_status'     => 'waiting_payment_confirmation',
                'discount_coupon_id'    => null,
            ]
        );
});

it('stores not informed placeholders for optional empty profile fields', function () {
    $formStoreRoute = route('form.store');
    $formThankYouRoute = route('form.thank-you');

    config([
            'services.turnstile.secret' => 'test-secret',
            'services.stripe.price_in_cents' => 3500,
        ]);
    Turnstile::fake();
    bindStripeClientForFormStore(
            'pi_test_init', 
            3500, 
            [
                'goviral_base_cents' => '3500',
                'discount_coupon_id' => '',
            ]
        );

    $validPayload = 

    $payload = validFormPayload();
    $payload = array_merge(
                        $payload, 
                        [
                            'tiktok_username'       => '',
                            'bio'                   => '',
                            'video_url_1'           => '',
                            'video_url_2'           => '',
                            'video_url_3'           => '',
                            'cf-turnstile-response' => Turnstile::dummy(),
                        ]
                    );

    $this->withSession([
                'locale' => 'pt'
            ])
        ->post($formStoreRoute, $payload)
        ->assertOk()
        ->assertJson([
                'thankYouUrl' => $formThankYouRoute,
            ]);

    $this->assertDatabaseHas(
            'analysis_requests', 
            [
                'email'             => 'creator@gmail.com',
                'tiktok_username'   => '<Not Informed>',
                'bio'               => '<Not Informed>',
                'video_url_1'       => '<Not Informed>',
                'video_url_2'       => '<Not Informed>',
                'video_url_3'       => '<Not Informed>',
                'locale'            => 'pt',
                'payment_status'    => 'pending',
                'processing_status' => 'waiting_payment_confirmation',
            ]
        );
});

it('rejects form store when CSRF token is invalid', function () {
    $formIndexRoute = route('form.index');
    $formStoreRoute = route('form.store');

    config(['services.turnstile.secret' => 'test-secret']);
    Turnstile::fake();

    $this->get($formIndexRoute);

    $payload = validFormPayload();
    $payload = array_merge(
                    $payload, 
                    [
                        'cf-turnstile-response' => Turnstile::dummy(),
                        '_token' => 'invalid-csrf-token',
                    ]
                );

    $this->post($formStoreRoute, $payload)
        ->assertStatus(419);

    $analysisCount = AnalysisRequest::count();
    expect($analysisCount)
        ->toBe(0);
})
->skip('CSRF enforcement in test env depends on session/cookie propagation; app has validateCsrfTokens for web routes.');

it('does not store analysis request when payload is invalid', function () {
    $formStoreRoute = route('form.store');

    config([
            'services.turnstile.secret' => 'test-secret'
        ]);
    Turnstile::fake();

    $payload = validFormPayload();
    $payload = array_merge(
                        $payload, 
                        [
                            'cf-turnstile-response' => Turnstile::dummy(),
                            'email' => 'invalid-email',
                            'video_url_1' => 'invalid-url',
                        ]
                    );

    $this->withSession([
            'locale' => 'pt'
        ])
        ->postJson($formStoreRoute, $payload)
        ->assertStatus(422)
        ->assertJsonValidationErrors([
                'email', 'video_url_1'
            ]);

    $analysisCount = AnalysisRequest::count();
    expect($analysisCount)
        ->toBe(0);
});

it('returns 422 when aspiring_niche is missing so payment is never confirmed twice', function () {
    $formStoreRoute = route('form.store');

    config([
            'services.turnstile.secret' => 'test-secret'
        ]);
    Turnstile::fake();

    $payload = validFormPayload();
    $payload = array_merge(
                        $payload, 
                        [
                            'cf-turnstile-response' => Turnstile::dummy(),
                            'aspiring_niche' => '',
                        ]
                    );

    $this->withSession([
                'locale' => 'en'
            ])
        ->postJson($formStoreRoute, $payload)
        ->assertStatus(422)
        ->assertJsonValidationErrors([
            'aspiring_niche'
        ]);

    $analysisCount = AnalysisRequest::count();
    expect($analysisCount)
        ->toBe(0);
});

it('returns 422 when turnstile token is missing or invalid and turnstile is configured', function () {
    $formStoreRoute = route('form.store');

    config([
            'services.turnstile.secret' => 'test-secret'
        ]);
    Turnstile::fake()
        ->fail();

    $payload = validFormPayload();
    $payload = array_merge(
                    $payload, 
                    [
                        'cf-turnstile-response' => Turnstile::dummy(),
                    ]
                );

    $this->withSession([
                'locale' => 'en'
            ])
        ->postJson($formStoreRoute, $payload)
        ->assertStatus(422)
        ->assertJsonValidationErrors([
                'cf-turnstile-response'
            ]);

    $analysisCount = AnalysisRequest::count();
    expect($analysisCount)
        ->toBe(0);
});

it('returns 422 when payment intent retrieval fails in store', function () {
    $formStoreRoute = route('form.store');

    config([
            'services.stripe.price_in_cents'    => 3500,
            'services.stripe.currency'          => 'usd',
            'cashier.key'                       => 'pk_test_example',
            'cashier.secret'                    => 'sk_test_example',
            'services.turnstile.secret'         => 'test-secret',
        ]);
    Turnstile::fake();

    bindStripeClientForFormStoreRetrieveFailure();

    $payload                          = validFormPayload();
    $payload['cf-turnstile-response'] = Turnstile::dummy();

    $response = $this->withSession([
                        'locale' => 'en'
                    ])
                    ->postJson($formStoreRoute, $payload);

    $response
        ->assertStatus(422)
        ->assertJson([
                'message' => trans('form.payment_confirm_error'),
            ]);

    $analysisCount = AnalysisRequest::count();
    expect($analysisCount)
        ->toBe(0);
});

it('returns 422 when base cents in metadata does not match configured price', function () {
    $formStoreRoute = route('form.store');

    config([
            'services.stripe.price_in_cents'    => 3500,
            'services.stripe.currency'          => 'usd',
            'cashier.key'                       => 'pk_test_example',
            'cashier.secret'                    => 'sk_test_example',
            'services.turnstile.secret'         => 'test-secret',
        ]);
    Turnstile::fake();

    bindStripeClientForFormStore(
            'pi_test_init', 
            3500, 
            [
                'goviral_base_cents' => '9999',
                'discount_coupon_id' => '',
            ]
        );

    $payload                          = validFormPayload();
    $payload['cf-turnstile-response'] = Turnstile::dummy();

    $this->withSession([
                'locale' => 'en'
            ])
        ->postJson($formStoreRoute, $payload)
        ->assertStatus(422)
        ->assertJson([
            'message' => trans('form.payment_confirm_error'),
        ]);

    $analysisCount = AnalysisRequest::count();
    expect($analysisCount)
        ->toBe(0);
});

it('returns 422 when coupon id in payment intent metadata does not resolve to coupon', function () {
    $formStoreRoute = route('form.store');

    config([
            'services.stripe.price_in_cents'    => 3500,
            'services.stripe.currency'          => 'usd',
            'cashier.key'                       => 'pk_test_example',
            'cashier.secret'                    => 'sk_test_example',
            'services.turnstile.secret'         => 'test-secret',
        ]);
    Turnstile::fake();

    $uuid = (string) \Ramsey\Uuid\Uuid::uuid4();
    bindStripeClientForFormStore(
            'pi_test_init', 
            3500, 
            [
                'goviral_base_cents' => '3500',
                'discount_coupon_id' => $uuid,
            ]
        );

    $payload                          = validFormPayload();
    $payload['cf-turnstile-response'] = Turnstile::dummy();

    $this->withSession([
                'locale' => 'en'
            ])
        ->postJson($formStoreRoute, $payload)
        ->assertStatus(422)
        ->assertJson([
                'message' => trans('form.coupon_invalid'),
            ]);

    $analysisCount = AnalysisRequest::count();
    expect($analysisCount)
        ->toBe(0);
});

it('returns 422 when discounted amount does not match metadata coupon', function () {
    $formStoreRoute = route('form.store');
    config([
            'services.stripe.price_in_cents'    => 3500,
            'services.stripe.currency'          => 'usd',
            'cashier.key'                       => 'pk_test_example',
            'cashier.secret'                    => 'sk_test_example',
            'services.turnstile.secret'         => 'test-secret',
        ]);
    Turnstile::fake();

    $coupon = DiscountCoupon::factory()
                ->create([
                        'value' => 20
                    ]);

    bindStripeClientForFormStore(
            'pi_test_init', 
            3000, 
            [ // wrong discounted amount
                'goviral_base_cents' => '3500',
                'discount_coupon_id' => $coupon->id,
            ]
        );

    $payload                          = validFormPayload();
    $payload['cf-turnstile-response'] = Turnstile::dummy();

    $message = trans('form.payment_confirm_error');
    $this->withSession([
                'locale' => 'en'
            ])
        ->postJson($formStoreRoute, $payload)
        ->assertStatus(422)
        ->assertJson([
            'message' => $message,
        ]);

    $analysisCount = AnalysisRequest::count();
    expect($analysisCount)
        ->toBe(0);
});

it('returns 422 when amount does not match base cents and there is no coupon', function () {
    $formStoreRoute = route('form.store');
    config([
            'services.stripe.price_in_cents'    => 3500,
            'services.stripe.currency'          => 'usd',
            'cashier.key'                       => 'pk_test_example',
            'cashier.secret'                    => 'sk_test_example',
            'services.turnstile.secret'         => 'test-secret',
        ]);
    Turnstile::fake();

    bindStripeClientForFormStore(
            'pi_test_init', 
            3400, 
            [ // wrong base amount
                'goviral_base_cents' => '3500',
                'discount_coupon_id' => '',
            ]
        );

    $payload                          = validFormPayload();
    $payload['cf-turnstile-response'] = Turnstile::dummy();

    $message = trans('form.payment_confirm_error');
    $this->withSession([
                'locale' => 'en'
            ])
        ->postJson($formStoreRoute, $payload)
        ->assertStatus(422)
        ->assertJson([
            'message' => $message,
        ]);

    $analysisCount = AnalysisRequest::count();
    expect($analysisCount)
        ->toBe(0);
});

it('stores analysis request with discount_coupon_id when payment intent metadata is valid', function () {
    $formStoreRoute = route('form.store');
    config([
            'services.stripe.price_in_cents'    => 3500,
            'services.stripe.currency'          => 'usd',
            'cashier.key'                       => 'pk_test_example',
            'cashier.secret'                    => 'sk_test_example',
            'services.turnstile.secret'         => 'test-secret',
        ]);
    Turnstile::fake();

    $coupon     = DiscountCoupon::factory()
                    ->create([
                            'value' => 10
                        ]);
    $base       = 3500;
    $discounted = DiscountCoupon::discountedAmountCents($base, $coupon->value);

    bindStripeClientForFormStore(
            'pi_test_init', 
            $discounted, 
            [
                'goviral_base_cents' => (string) $base,
                'discount_coupon_id' => $coupon->id,
            ]
        );

    $payload                          = validFormPayload();
    $payload['cf-turnstile-response'] = Turnstile::dummy();

    $this->withSession([
                'locale' => 'en'
            ])
        ->postJson($formStoreRoute, $payload)
        ->assertOk()
        ->assertJsonStructure([
                'analysisRequestId',
                'thankYouUrl',
            ]);

    $saved = AnalysisRequest::firstOrFail();

    $discountCouponId = $saved->discount_coupon_id;
    expect($discountCouponId)
        ->toBe($coupon->id);
});

it('returns 422 when turnstile token is missing and turnstile is configured', function () {
    $formStoreRoute = route('form.store');
    config([
            'services.turnstile.secret' => 'test-secret'
        ]);

    $response = $this->withSession([
                            'locale' => 'en'
                        ])
                    ->postJson($formStoreRoute, validFormPayload());

    $response->assertStatus(422)
        ->assertJsonValidationErrors([
                'cf-turnstile-response'
            ]);

    $count = AnalysisRequest::count();
    expect($count)
        ->toBe(0);
});

it('renders thank you page with translated content when accessed with flow', function () {
    $thankYouRoute = route('form.thank-you');


    $translationTitle = '¡Gracias! Tu solicitud está confirmada.';
    $translationMessage = 'Tu informe de crecimiento será enviado a tu correo en un plazo de 30 minutos.';
    $translationCta = 'Volver al inicio';

    $this->withSession([
                'locale' => 'es', 
                'thank_you_allowed' => true
            ])
        ->get($thankYouRoute)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Form/ThankYou')
                                                ->where('translations.title', $translationTitle)
                                                ->where('translations.message', $translationMessage)
                                                ->where('translations.cta', $translationCta)
        );
});

it('redirects to home when accessing thank-you without flow', function () {
    $thankYouRoute = route('form.thank-you');
    $homeRoute = route('home');
    $this->get($thankYouRoute)
        ->assertRedirect($homeRoute);
});

it('returns payment init error when stripe keys are missing', function () {
    $paymentIntentRoute = route('form.payment-intent');
    config([
            'cashier.key' => null,
            'cashier.secret' => null,
        ]);

    $error = trans('form.payment_init_error');
    $this->get($paymentIntentRoute)
        ->assertStatus(500)
        ->assertJson([
                'message' => $error,
            ]);
});

it('returns payment init error when stripe api fails', function () {
    $paymentIntentRoute = route('form.payment-intent');
    config([
            'cashier.key' => 'pk_test_example',
            'cashier.secret' => 'sk_test_example',
        ]);

    $error = trans('form.payment_init_error');
    $this->get($paymentIntentRoute)
        ->assertStatus(500)
        ->assertJson([
            'message' => $error,
        ]);
});

it('returns payment intent payload when stripe api succeeds', function () {
    $paymentIntentRoute = route('form.payment-intent');
    config([
            'services.stripe.price_in_cents' => 3500,
            'services.stripe.currency' => 'usd',
            'cashier.key' => 'pk_test_example',
            'cashier.secret' => 'sk_test_example',
        ]);

    bindStripeClientForPaymentIntentCreate([
            'amountCents'       => 3500,
            'currency'          => 'usd',
            'baseCents'         => '3500',
            'expectsCouponId'   => false,
            'paymentIntentId'   => 'pi_mock_test_123',
            'clientSecret'      => 'pi_mock_secret_123',
        ]);

    $this->get($paymentIntentRoute)
        ->assertOk()
        ->assertJsonStructure([
            'paymentIntentId',
            'clientSecret',
            'publishableKey',
            'amountCents',
            'currency',
        ])
        ->assertJson([
            'paymentIntentId'   => 'pi_mock_test_123',
            'clientSecret'      => 'pi_mock_secret_123',
            'publishableKey'    => 'pk_test_example',
            'amountCents'       => 3500,
            'currency'          => 'usd',
        ]);
});

it('returns discounted payment intent payload when valid coupon code is provided', function () {
    $paymentIntentRoute = route(
                                'form.payment-intent', 
                                [
                                    'coupon_code' => 'save20'
                                ]
                            );
    config([
            'services.stripe.price_in_cents'    => 10000,
            'services.stripe.currency'          => 'usd',
            'cashier.key'                       => 'pk_test_example',
            'cashier.secret'                    => 'sk_test_example',
        ]);

    $coupon = DiscountCoupon::factory()
                ->create([
                        'code' => 'SAVE20',
                        'value' => 20,
                    ]);

    bindStripeClientForPaymentIntentCreate([
            'amountCents'       => 8000,
            'currency'          => 'usd',
            'baseCents'         => '10000',
            'expectsCouponId'   => true,
            'paymentIntentId'   => 'pi_mock_discounted',
            'clientSecret'      => 'pi_mock_discounted_secret',
        ]);

    $this->get($paymentIntentRoute)
        ->assertOk()
        ->assertJson([
            'paymentIntentId'   => 'pi_mock_discounted',
            'clientSecret'      => 'pi_mock_discounted_secret',
            'publishableKey'    => 'pk_test_example',
            'amountCents'       => 8000,
            'currency'          => 'usd',
            'discountPercent'   => $coupon->value,
        ]);
});

function bindStripeClientForFormStore(string $paymentIntentId, int $amountCents, array $metadata): void
{
    app()->bind(StripeClient::class, function () use ($paymentIntentId, $amountCents, $metadata) {
        $paymentIntents = \Mockery::mock();
        $paymentIntents
            ->shouldReceive('retrieve')
            ->once()
            ->with($paymentIntentId)
            ->andReturn((object) [
                'amount' => $amountCents,
                'metadata' => \Mockery::mock([
                    'toArray' => $metadata,
                ]),
            ]);

        $stripe = \Mockery::mock(StripeClient::class);
        $stripe->paymentIntents = $paymentIntents;

        return $stripe;
    });
}

function bindStripeClientForFormStoreRetrieveFailure(): void
{
    app()
        ->bind(StripeClient::class, function () {
            $paymentIntents = \Mockery::mock();
            $paymentIntents->shouldReceive('retrieve')
                ->once()
                ->andThrow(new RuntimeException('Stripe failure'));

            $stripe = \Mockery::mock(StripeClient::class);
            $stripe->paymentIntents = $paymentIntents;

            return $stripe;
        });
}

/**
 * Binds a mocked Stripe client for payment intent creation assertions.
 *
 * Expected keys in $expectations:
 * - amountCents (int): expected amount sent to Stripe.
 * - currency (string): expected currency sent to Stripe.
 * - baseCents (string): expected metadata.goviral_base_cents value.
 * - expectsCouponId (bool): true when metadata.discount_coupon_id must be non-empty.
 * - paymentIntentId (string): mocked id returned by Stripe.
 * - clientSecret (string): mocked client secret returned by Stripe.
 *
 * @param array{
 *     amountCents:int,
 *     currency:string,
 *     baseCents:string,
 *     expectsCouponId:bool,
 *     paymentIntentId:string,
 *     clientSecret:string
 * } $expectations
 */
function bindStripeClientForPaymentIntentCreate(array $expectations): void
{
    app()
        ->bind(StripeClient::class, function() use ($expectations) {
            $paymentIntents = \Mockery::mock();
            $paymentIntents->shouldReceive('create')
                ->once()
                ->andReturnUsing(function(array $payload) use ($expectations): object {
                    $amountCents        = $expectations['amountCents'];
                    $currency           = $expectations['currency'];
                    $baseCents          = $expectations['baseCents'];
                    $expectsCouponId    = $expectations['expectsCouponId'];
                    $paymentIntentId    = $expectations['paymentIntentId'];
                    $clientSecret       = $expectations['clientSecret'];

                    expect($payload)
                        ->toMatchArray([
                                'amount' => $amountCents,
                                'currency' => $currency,
                                'automatic_payment_methods' => ['enabled' => true],
                            ]);

                    $baseCentsPayload = $payload['metadata']['goviral_base_cents'] ?? null;
                    expect($baseCentsPayload)
                        ->toBe($baseCents);

                    $discountCouponId = $payload['metadata']['discount_coupon_id'] ?? '';
                    if ($expectsCouponId) {
                        expect($discountCouponId)
                            ->not
                            ->toBe('');
                    } else {
                        expect($discountCouponId)
                            ->toBe('');
                    }

                    return (object) [
                        'id' => $paymentIntentId,
                        'client_secret' => $clientSecret,
                    ];
                });

            $stripe = \Mockery::mock(StripeClient::class);
            $stripe->paymentIntents = $paymentIntents;

            return $stripe;
        }
    );
}

function validFormPayload(): array
{
    return [
        'email'             => 'creator@gmail.com',
        'tiktok_username'   => '@creator',
        'bio'               => 'Bio content for analysis.',
        'aspiring_niche'    => 'Lifestyle',
        'video_url_1'       => 'https://example.com/video-1',
        'video_url_2'       => 'https://example.com/video-2',
        'video_url_3'       => 'https://example.com/video-3',
        'notes'             => 'Optional notes here',
        'payment_intent_id' => 'pi_test_init',
    ];
}
