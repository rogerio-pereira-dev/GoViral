<?php

use App\Http\Requests\Form\StoreAnalysisRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

it('authorizes the request', function () {
    $request = new StoreAnalysisRequest;

    expect($request->authorize())->toBeTrue();
});

it('defines validation rules for analysis request input', function () {
    $request = new StoreAnalysisRequest;

    expect($request->rules())->toBe([
        'email' => ['required', 'string', 'email:rfc,dns', 'max:255'],
        'tiktok_username' => ['nullable', 'string', 'max:255'],
        'bio' => ['nullable', 'string', 'max:5000'],
        'aspiring_niche' => ['required', 'string', 'max:255'],
        'video_url_1' => ['nullable', 'url', 'max:2048'],
        'video_url_2' => ['nullable', 'url', 'max:2048'],
        'video_url_3' => ['nullable', 'url', 'max:2048'],
        'notes' => ['nullable', 'string', 'max:5000'],
    ]);
});

it('validates required fields and expected error messages', function () {
    app()->setLocale('en');

    $validator = Validator::make([], (new StoreAnalysisRequest)->rules());

    expect($validator->fails())->toBeTrue();

    $requiredFields = [
        'email',
        'aspiring_niche',
    ];

    foreach ($requiredFields as $field) {
        expect($validator->errors()->first($field))
            ->toBe(trans('validation.required', ['attribute' => str_replace('_', ' ', $field)]));
    }
});

it('validates format, max length and nullable notes with expected messages', function () {
    app()->setLocale('en');

    $payload = [
        'email' => 'invalid-email',
        'tiktok_username' => Str::repeat('a', 256),
        'bio' => Str::repeat('a', 5001),
        'aspiring_niche' => Str::repeat('a', 256),
        'video_url_1' => 'not-a-url',
        'video_url_2' => 'also-not-a-url',
        'video_url_3' => 'still-not-a-url',
        'notes' => null,
    ];

    $validator = Validator::make($payload, (new StoreAnalysisRequest)->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('email'))
        ->toBe(trans('validation.email', ['attribute' => 'email']))
        ->and($validator->errors()->first('tiktok_username'))
        ->toBe(trans('validation.max.string', ['attribute' => 'tiktok username', 'max' => 255]))
        ->and($validator->errors()->first('bio'))
        ->toBe(trans('validation.max.string', ['attribute' => 'bio', 'max' => 5000]))
        ->and($validator->errors()->first('aspiring_niche'))
        ->toBe(trans('validation.max.string', ['attribute' => 'aspiring niche', 'max' => 255]))
        ->and($validator->errors()->first('video_url_1'))
        ->toBe(trans('validation.url', ['attribute' => 'video url 1']))
        ->and($validator->errors()->first('video_url_2'))
        ->toBe(trans('validation.url', ['attribute' => 'video url 2']))
        ->and($validator->errors()->first('video_url_3'))
        ->toBe(trans('validation.url', ['attribute' => 'video url 3']))
        ->and($validator->errors()->has('notes'))
        ->toBeFalse();
});

it('accepts nullable profile fields when omitted', function () {
    $payload = [
        'email' => 'jane@gmail.com',
        'aspiring_niche' => 'Fitness',
        'notes' => null,
    ];

    $validator = Validator::make($payload, (new StoreAnalysisRequest)->rules());

    expect($validator->fails())->toBeFalse();
});

it('validates max length for video_url_2 with expected error message', function () {
    app()->setLocale('en');

    $payload = validPayload();
    $payload['video_url_2'] = 'https://example.com/'.Str::repeat('a', 2100);

    $validator = Validator::make($payload, (new StoreAnalysisRequest)->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('video_url_2'))
        ->toBe(trans('validation.max.string', ['attribute' => 'video url 2', 'max' => 2048]));
});

it('validates string rule and expected error messages', function () {
    app()->setLocale('en');

    $payload = validPayload();
    $payload['email'] = ['not', 'a', 'string'];
    $payload['tiktok_username'] = ['not', 'a', 'string'];
    $payload['bio'] = ['not', 'a', 'string'];
    $payload['aspiring_niche'] = ['not', 'a', 'string'];
    $payload['notes'] = ['not', 'a', 'string'];

    $validator = Validator::make($payload, (new StoreAnalysisRequest)->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('email'))
        ->toBe(trans('validation.string', ['attribute' => 'email']))
        ->and($validator->errors()->first('tiktok_username'))
        ->toBe(trans('validation.string', ['attribute' => 'tiktok username']))
        ->and($validator->errors()->first('bio'))
        ->toBe(trans('validation.string', ['attribute' => 'bio']))
        ->and($validator->errors()->first('aspiring_niche'))
        ->toBe(trans('validation.string', ['attribute' => 'aspiring niche']))
        ->and($validator->errors()->first('notes'))
        ->toBe(trans('validation.string', ['attribute' => 'notes']));
});

it('sanitizes potentially unsafe fields before validation', function () {
    $request = new StoreAnalysisRequest;

    $request->merge([
        'email' => '  <b>jane@example.com</b>  ',
        'tiktok_username' => '  <script>alert(1)</script>@jane  ',
        'bio' => '  <p>Hello <strong>world</strong></p>  ',
        'aspiring_niche' => '  <img src=x onerror=alert(1)>Fitness  ',
        'video_url_1' => '  https://example.com/video-1  ',
        'video_url_2' => '  https://example.com/video-2  ',
        'video_url_3' => '  https://example.com/video-3  ',
        'notes' => '  <iframe src="javascript:alert(1)"></iframe>note  ',
    ]);

    $method = new ReflectionMethod(StoreAnalysisRequest::class, 'prepareForValidation');
    $method->setAccessible(true);
    $method->invoke($request);

    expect($request->input('email'))->toBe('jane@example.com')
        ->and($request->input('tiktok_username'))->toBe('alert(1)@jane')
        ->and($request->input('bio'))->toBe('Hello world')
        ->and($request->input('aspiring_niche'))->toBe('Fitness')
        ->and($request->input('video_url_1'))->toBe('https://example.com/video-1')
        ->and($request->input('video_url_2'))->toBe('https://example.com/video-2')
        ->and($request->input('video_url_3'))->toBe('https://example.com/video-3')
        ->and($request->input('notes'))->toBe('note');
});

it('converts non-string values to null during sanitization', function () {
    $request = new StoreAnalysisRequest;

    $request->merge([
        'email' => 123,
        'tiktok_username' => ['array'],
        'bio' => ['array'],
        'aspiring_niche' => false,
        'video_url_1' => 1.2,
        'video_url_2' => ['array'],
        'video_url_3' => true,
        'notes' => null,
    ]);

    $method = new ReflectionMethod(StoreAnalysisRequest::class, 'prepareForValidation');
    $method->setAccessible(true);
    $method->invoke($request);

    expect($request->input('email'))->toBeNull()
        ->and($request->input('tiktok_username'))->toBeNull()
        ->and($request->input('bio'))->toBeNull()
        ->and($request->input('aspiring_niche'))->toBeNull()
        ->and($request->input('video_url_1'))->toBeNull()
        ->and($request->input('video_url_2'))->toBeNull()
        ->and($request->input('video_url_3'))->toBeNull()
        ->and($request->input('notes'))->toBeNull();
});

function validPayload(): array
{
    return [
        'email' => 'jane@example.com',
        'tiktok_username' => '@janedoe',
        'bio' => Str::repeat('a', 100),
        'aspiring_niche' => 'Fitness',
        'video_url_1' => 'https://example.com/video-1',
        'video_url_2' => 'https://example.com/video-2',
        'video_url_3' => 'https://example.com/video-3',
        'notes' => 'Optional notes',
    ];
}
