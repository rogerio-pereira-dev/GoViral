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
        'payment_intent_id' => ['required', 'string', 'max:255'],
        'cf-turnstile-response' => ['nullable', 'string'],
    ]);
});

it('defines localized validation messages and attributes', function () {
    $request = new StoreAnalysisRequest;

    app()->setLocale('en');

    expect($request->messages())->toBe([
        'required' => trans('form.validation.required'),
        'string' => trans('form.validation.string'),
        'email' => trans('form.validation.email'),
        'url' => trans('form.validation.url'),
        'max' => trans('form.validation.max'),
    ]);

    expect($request->attributes())->toBe([
        'email' => trans('form.email_label'),
        'tiktok_username' => trans('form.tiktok_username_label'),
        'bio' => trans('form.bio_label'),
        'aspiring_niche' => trans('form.aspiring_niche_label'),
        'video_url_1' => trans('form.video_url_1_label'),
        'video_url_2' => trans('form.video_url_2_label'),
        'video_url_3' => trans('form.video_url_3_label'),
        'notes' => trans('form.notes_label'),
        'payment_intent_id' => trans('form.payment_card_label'),
    ]);
});

it('validates required fields and expected error messages', function () {
    app()->setLocale('en');

    $request = new StoreAnalysisRequest;

    $validator = Validator::make([], $request->rules(), $request->messages(), $request->attributes());

    expect($validator->fails())->toBeTrue();

    $requiredFields = [
        'email',
        'aspiring_niche',
        'payment_intent_id',
    ];

    foreach ($requiredFields as $field) {
        expect($validator->errors()->first($field))
            ->toBe(trans('form.validation.required', ['attribute' => $request->attributes()[$field]]));
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
        'payment_intent_id' => null,
    ];

    $request = new StoreAnalysisRequest;

    $validator = Validator::make($payload, $request->rules(), $request->messages(), $request->attributes());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('email'))
        ->toBe(trans('form.validation.email', ['attribute' => $request->attributes()['email']]))
        ->and($validator->errors()->first('tiktok_username'))
        ->toBe(trans('form.validation.max', ['attribute' => $request->attributes()['tiktok_username'], 'max' => 255]))
        ->and($validator->errors()->first('bio'))
        ->toBe(trans('form.validation.max', ['attribute' => $request->attributes()['bio'], 'max' => 5000]))
        ->and($validator->errors()->first('aspiring_niche'))
        ->toBe(trans('form.validation.max', ['attribute' => $request->attributes()['aspiring_niche'], 'max' => 255]))
        ->and($validator->errors()->first('video_url_1'))
        ->toBe(trans('form.validation.url', ['attribute' => $request->attributes()['video_url_1']]))
        ->and($validator->errors()->first('video_url_2'))
        ->toBe(trans('form.validation.url', ['attribute' => $request->attributes()['video_url_2']]))
        ->and($validator->errors()->first('video_url_3'))
        ->toBe(trans('form.validation.url', ['attribute' => $request->attributes()['video_url_3']]))
        ->and($validator->errors()->has('notes'))
        ->toBeFalse();
});

it('accepts nullable profile fields when omitted', function () {
    $payload = [
        'email' => 'jane@gmail.com',
        'aspiring_niche' => 'Fitness',
        'notes' => null,
        'payment_intent_id' => 'pi_test_init',
    ];

    $validator = Validator::make($payload, (new StoreAnalysisRequest)->rules());

    expect($validator->fails())->toBeFalse();
});

it('validates max length for video_url_2 with expected error message', function () {
    app()->setLocale('en');

    $payload = validPayload();
    $payload['video_url_2'] = 'https://example.com/'.Str::repeat('a', 2100);

    $request = new StoreAnalysisRequest;

    $validator = Validator::make($payload, $request->rules(), $request->messages(), $request->attributes());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('video_url_2'))
        ->toBe(trans('form.validation.max', ['attribute' => $request->attributes()['video_url_2'], 'max' => 2048]));
});

it('validates string rule and expected error messages', function () {
    app()->setLocale('en');

    $payload = validPayload();
    $payload['email'] = ['not', 'a', 'string'];
    $payload['tiktok_username'] = ['not', 'a', 'string'];
    $payload['bio'] = ['not', 'a', 'string'];
    $payload['aspiring_niche'] = ['not', 'a', 'string'];
    $payload['notes'] = ['not', 'a', 'string'];

    $request = new StoreAnalysisRequest;

    $validator = Validator::make($payload, $request->rules(), $request->messages(), $request->attributes());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('email'))
        ->toBe(trans('form.validation.string', ['attribute' => $request->attributes()['email']]))
        ->and($validator->errors()->first('tiktok_username'))
        ->toBe(trans('form.validation.string', ['attribute' => $request->attributes()['tiktok_username']]))
        ->and($validator->errors()->first('bio'))
        ->toBe(trans('form.validation.string', ['attribute' => $request->attributes()['bio']]))
        ->and($validator->errors()->first('aspiring_niche'))
        ->toBe(trans('form.validation.string', ['attribute' => $request->attributes()['aspiring_niche']]))
        ->and($validator->errors()->first('notes'))
        ->toBe(trans('form.validation.string', ['attribute' => $request->attributes()['notes']]));
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
        'payment_intent_id' => '  <b>pi_test_init</b>  ',
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
        ->and($request->input('notes'))->toBe('note')
        ->and($request->input('payment_intent_id'))->toBe('pi_test_init');
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
        'payment_intent_id' => ['array'],
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
        ->and($request->input('notes'))->toBeNull()
        ->and($request->input('payment_intent_id'))->toBeNull();
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
        'payment_intent_id' => 'pi_test_init',
    ];
}
