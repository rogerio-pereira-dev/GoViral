<?php

use App\Models\AnalysisRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Stripe\StripeClient;

uses(RefreshDatabase::class);

it('renders the start growth form page with current locale', function () {
    $response = $this
        ->withSession(['locale' => 'pt'])
        ->get(route('form.index'));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Form/StartGrowth')
            ->where('locale', 'pt')
        );
});

it('stores a new analysis request and returns checkout payload', function () {
    $response = $this
        ->withSession(['locale' => 'es'])
        ->post(route('form.store'), validFormPayload());

    $response
        ->assertOk()
        ->assertJsonStructure([
            'analysisRequestId',
            'thankYouUrl',
        ])
        ->assertJson([
            'thankYouUrl' => route('form.thank-you'),
        ]);

    $this->assertDatabaseHas('analysis_requests', [
        'email' => 'creator@gmail.com',
        'tiktok_username' => '@creator',
        'bio' => 'Bio content for analysis.',
        'aspiring_niche' => 'Lifestyle',
        'video_url_1' => 'https://example.com/video-1',
        'video_url_2' => 'https://example.com/video-2',
        'video_url_3' => 'https://example.com/video-3',
        'notes' => 'Optional notes here',
        'locale' => 'es',
        'payment_status' => 'pending',
        'processing_status' => 'waiting_payment_confirmation',
    ]);
});

it('stores not informed placeholders for optional empty profile fields', function () {
    $response = $this
        ->withSession(['locale' => 'pt'])
        ->post(route('form.store'), [
            ...validFormPayload(),
            'tiktok_username' => '',
            'bio' => '',
            'video_url_1' => '',
            'video_url_2' => '',
            'video_url_3' => '',
        ]);

    $response
        ->assertOk()
        ->assertJson([
            'thankYouUrl' => route('form.thank-you'),
        ]);

    $this->assertDatabaseHas('analysis_requests', [
        'email' => 'creator@gmail.com',
        'tiktok_username' => '<Not Informed>',
        'bio' => '<Not Informed>',
        'video_url_1' => '<Not Informed>',
        'video_url_2' => '<Not Informed>',
        'video_url_3' => '<Not Informed>',
        'locale' => 'pt',
        'payment_status' => 'pending',
        'processing_status' => 'waiting_payment_confirmation',
    ]);
});

it('does not store analysis request when payload is invalid', function () {
    $response = $this
        ->withSession(['locale' => 'pt'])
        ->postJson(route('form.store'), [
            ...validFormPayload(),
            'email' => 'invalid-email',
            'video_url_1' => 'invalid-url',
        ]);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email', 'video_url_1']);

    expect(AnalysisRequest::count())->toBe(0);
});

it('renders thank you page with translated content', function () {
    $response = $this
        ->withSession(['locale' => 'es'])
        ->get(route('form.thank-you'));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Form/ThankYou')
            ->where('translations.title', '¡Gracias! Tu solicitud está confirmada.')
            ->where('translations.message', 'Tu informe de crecimiento será enviado a tu correo en un plazo de 30 minutos.')
            ->where('translations.cta', 'Volver al inicio')
        );
});

it('returns payment intent payload on initialization endpoint', function () {
    $response = $this->get(route('form.payment-intent'));

    $response
        ->assertOk()
        ->assertJsonStructure([
            'skipPayment',
            'paymentIntentId',
            'clientSecret',
            'publishableKey',
            'amountCents',
            'currency',
        ]);
});

it('returns payment init error when fake mode is disabled and stripe keys are missing', function () {
    config([
        'services.stripe.fake_intent_on_testing' => false,
        'cashier.key' => null,
        'cashier.secret' => null,
    ]);

    $response = $this->get(route('form.payment-intent'));

    $response
        ->assertStatus(500)
        ->assertJson([
            'message' => trans('form.payment_init_error'),
        ]);
});

it('returns payment init error when provider fails in real mode', function () {
    config([
        'services.stripe.fake_intent_on_testing' => false,
        'cashier.key' => 'pk_test_example',
        'cashier.secret' => 'sk_test_example',
    ]);

    $response = $this->get(route('form.payment-intent'));

    $response
        ->assertStatus(500)
        ->assertJson([
            'message' => trans('form.payment_init_error'),
        ]);
});

it('returns live payment intent payload when provider succeeds in real mode', function () {
    config([
        'services.stripe.fake_intent_on_testing' => false,
        'services.stripe.price_in_cents' => 3500,
        'cashier.key' => 'pk_test_example',
        'cashier.secret' => 'sk_test_example',
    ]);

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
                        expect($payload)->toBe([
                            'amount' => 3500,
                            'currency' => 'usd',
                            'automatic_payment_methods' => ['enabled' => true],
                        ]);

                        return (object) [
                            'id' => 'pi_live_test',
                            'client_secret' => 'pi_live_secret_test',
                        ];
                    }
                };
            }
        };
    });

    $response = $this->get(route('form.payment-intent'));

    $response
        ->assertOk()
        ->assertJson([
            'skipPayment' => false,
            'paymentIntentId' => 'pi_live_test',
            'clientSecret' => 'pi_live_secret_test',
            'publishableKey' => 'pk_test_example',
            'amountCents' => 3500,
            'currency' => 'usd',
            'testScenario' => null,
        ]);
});

function validFormPayload(): array
{
    return [
        'email' => 'creator@gmail.com',
        'tiktok_username' => '@creator',
        'bio' => 'Bio content for analysis.',
        'aspiring_niche' => 'Lifestyle',
        'video_url_1' => 'https://example.com/video-1',
        'video_url_2' => 'https://example.com/video-2',
        'video_url_3' => 'https://example.com/video-3',
        'notes' => 'Optional notes here',
        'payment_intent_id' => 'pi_test_init',
    ];
}
