<?php

use App\Models\AnalysisRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

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

it('stores a new analysis request and redirects to thank you page', function () {
    $response = $this
        ->withSession(['locale' => 'es'])
        ->post(route('form.store'), validFormPayload());

    $response->assertRedirect('/thank-you');

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

    $response->assertRedirect('/thank-you');

    $this->assertDatabaseHas('analysis_requests', [
        'email' => 'creator@gmail.com',
        'tiktok_username' => '<Not Informed>',
        'bio' => '<Not Informed>',
        'video_url_1' => '<Not Informed>',
        'video_url_2' => '<Not Informed>',
        'video_url_3' => '<Not Informed>',
        'locale' => 'pt',
        'payment_status' => 'pending',
    ]);
});

it('does not store analysis request when payload is invalid', function () {
    $response = $this
        ->withSession(['locale' => 'pt'])
        ->from(route('form.index'))
        ->post(route('form.store'), [
            ...validFormPayload(),
            'email' => 'invalid-email',
            'video_url_1' => 'invalid-url',
        ]);

    $response
        ->assertRedirect(route('form.index'))
        ->assertSessionHasErrors(['email', 'video_url_1']);

    expect(AnalysisRequest::query()->count())->toBe(0);
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
    ];
}
