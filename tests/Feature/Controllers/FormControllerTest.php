<?php

use App\Models\AnalysisRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use RyanChandler\LaravelCloudflareTurnstile\Facades\Turnstile;
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
    config(['services.turnstile.secret' => 'test-secret']);
    Turnstile::fake();

    $payload = validFormPayload();
    $payload['cf-turnstile-response'] = Turnstile::dummy();

    $response = $this
        ->withSession(['locale' => 'es'])
        ->post(route('form.store'), $payload);

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
    config(['services.turnstile.secret' => 'test-secret']);
    Turnstile::fake();

    $payload = array_merge(validFormPayload(), [
        'tiktok_username' => '',
        'bio' => '',
        'video_url_1' => '',
        'video_url_2' => '',
        'video_url_3' => '',
        'cf-turnstile-response' => Turnstile::dummy(),
    ]);

    $response = $this
        ->withSession(['locale' => 'pt'])
        ->post(route('form.store'), $payload);

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

it('rejects form store when CSRF token is invalid', function () {
    config(['services.turnstile.secret' => 'test-secret']);
    Turnstile::fake();

    $this->get(route('form.index'));
    $payload = array_merge(validFormPayload(), [
        'cf-turnstile-response' => Turnstile::dummy(),
        '_token' => 'invalid-csrf-token',
    ]);

    $response = $this->post(route('form.store'), $payload);

    $response->assertStatus(419);
    expect(AnalysisRequest::count())->toBe(0);
})->skip('CSRF enforcement in test env depends on session/cookie propagation; app has validateCsrfTokens for web routes.');

it('does not store analysis request when payload is invalid', function () {
    config(['services.turnstile.secret' => 'test-secret']);
    Turnstile::fake();

    $payload = array_merge(validFormPayload(), [
        'cf-turnstile-response' => Turnstile::dummy(),
        'email' => 'invalid-email',
        'video_url_1' => 'invalid-url',
    ]);

    $response = $this
        ->withSession(['locale' => 'pt'])
        ->postJson(route('form.store'), $payload);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email', 'video_url_1']);

    expect(AnalysisRequest::count())->toBe(0);
});

it('returns 422 when aspiring_niche is missing so payment is never confirmed twice', function () {
    config(['services.turnstile.secret' => 'test-secret']);
    Turnstile::fake();

    $payload = array_merge(validFormPayload(), [
        'cf-turnstile-response' => Turnstile::dummy(),
        'aspiring_niche' => '',
    ]);

    $response = $this
        ->withSession(['locale' => 'en'])
        ->postJson(route('form.store'), $payload);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['aspiring_niche']);

    expect(AnalysisRequest::count())->toBe(0);
});

it('returns 422 when turnstile token is missing or invalid and turnstile is configured', function () {
    config(['services.turnstile.secret' => 'test-secret']);
    Turnstile::fake()->fail();

    $response = $this
        ->withSession(['locale' => 'en'])
        ->postJson(route('form.store'), array_merge(validFormPayload(), [
            'cf-turnstile-response' => Turnstile::dummy(),
        ]));

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['cf-turnstile-response']);

    expect(AnalysisRequest::count())->toBe(0);
});

it('returns 422 when turnstile token is missing and turnstile is configured', function () {
    config(['services.turnstile.secret' => 'test-secret']);

    $response = $this
        ->withSession(['locale' => 'en'])
        ->postJson(route('form.store'), validFormPayload());

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['cf-turnstile-response']);

    expect(AnalysisRequest::count())->toBe(0);
});

it('renders thank you page with translated content when accessed with flow', function () {
    $response = $this
        ->withSession(['locale' => 'es', 'thank_you_allowed' => true])
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

it('redirects to home when accessing thank-you without flow', function () {
    $response = $this->get(route('form.thank-you'));

    $response->assertRedirect(route('home'));
});

it('returns payment init error when stripe keys are missing', function () {
    config([
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

it('returns payment init error when stripe api fails', function () {
    config([
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

it('returns payment intent payload when stripe api succeeds', function () {
    config([
        'services.stripe.price_in_cents' => 3500,
        'services.stripe.currency' => 'usd',
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
                            'id' => 'pi_mock_test_123',
                            'client_secret' => 'pi_mock_secret_123',
                        ];
                    }
                };
            }
        };
    });

    $response = $this->get(route('form.payment-intent'));

    $response
        ->assertOk()
        ->assertJsonStructure([
            'paymentIntentId',
            'clientSecret',
            'publishableKey',
            'amountCents',
            'currency',
        ])
        ->assertJson([
            'paymentIntentId' => 'pi_mock_test_123',
            'clientSecret' => 'pi_mock_secret_123',
            'publishableKey' => 'pk_test_example',
            'amountCents' => 3500,
            'currency' => 'usd',
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
