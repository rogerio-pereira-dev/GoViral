<?php

use App\Http\Requests\Form\StoreAnalysisRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

beforeEach(function (): void {
    config(['services.turnstile.secret' => null]);
});

it('authorizes the request', function () {
    $request      = new StoreAnalysisRequest;
    $isAuthorized = $request->authorize();

    expect($isAuthorized)
        ->toBeTrue();
});

it('defines validation rules for analysis request input', function () {
    $request = new StoreAnalysisRequest;
    $rules   = $request->rules();

    expect($rules)
        ->toBe([
            'email'                 => ['required',     'string',   'email:rfc,dns', 'max:255'  ],
            'tiktok_username'       => ['nullable',     'string',   'max:255'                   ],
            'bio'                   => ['nullable',     'string',   'max:5000'                  ],
            'aspiring_niche'        => ['required',     'string',   'max:255'                   ],
            'video_url_1'           => ['nullable',     'url',      'max:2048'                  ],
            'video_url_2'           => ['nullable',     'url',      'max:2048'                  ],
            'video_url_3'           => ['nullable',     'url',      'max:2048'                  ],
            'notes'                 => ['nullable',     'string',   'max:5000'                  ],
            'payment_intent_id'     => ['required',     'string',   'max:255'                   ],
            'cf-turnstile-response' => ['nullable',     'string'                                ],
        ]);
});

it('defines localized validation messages and attributes', function () {
    $request = new StoreAnalysisRequest;

    app()->setLocale('en');

    $messages   = $request->messages();
    $attributes = $request->attributes();

    expect($messages)
        ->toBe([
            'required'  => trans('form.validation.required'),
            'string'    => trans('form.validation.string'),
            'email'     => trans('form.validation.email'),
            'url'       => trans('form.validation.url'),
            'max'       => trans('form.validation.max'),
        ]);

    expect($attributes)
        ->toBe([
            'email'                 => trans('form.email_label'),
            'tiktok_username'       => trans('form.tiktok_username_label'),
            'bio'                   => trans('form.bio_label'),
            'aspiring_niche'        => trans('form.aspiring_niche_label'),
            'video_url_1'           => trans('form.video_url_1_label'),
            'video_url_2'           => trans('form.video_url_2_label'),
            'video_url_3'           => trans('form.video_url_3_label'),
            'notes'                 => trans('form.notes_label'),
            'payment_intent_id'     => trans('form.payment_card_label'),
            'cf-turnstile-response' => trans('form.turnstile_label'),
        ]);
});

it('validates required fields and expected error messages', function () {
    app()->setLocale('en');

    $request    = new StoreAnalysisRequest;
    $rules      = $request->rules();
    $messages   = $request->messages();
    $attributes = $request->attributes();

    $validator = Validator::make([], $rules, $messages, $attributes);
    $fails     = $validator->fails();

    expect($fails)
        ->toBeTrue();

    $requiredFields = [
            'email',
            'aspiring_niche',
            'payment_intent_id',
        ];
    $errors     = $validator->errors();
    $attributes = $request->attributes();

    foreach ($requiredFields as $field) {
        $firstError = $errors->first($field);
        $attribute  = $attributes[$field];
        $expected   = trans('form.validation.required', ['attribute' => $attribute]);

        expect($firstError)
            ->toBe($expected);
    }
});

it('validates format, max length and nullable notes with expected messages', function () {
    app()->setLocale('en');

    $tiktokUsername = Str::repeat('a', 256);
    $bio            = Str::repeat('a', 5001);
    $aspiringNiche  = Str::repeat('a', 256);

    $payload = [
            'email'             => 'invalid-email',
            'tiktok_username'   => $tiktokUsername,
            'bio'               => $bio,
            'aspiring_niche'    => $aspiringNiche,
            'video_url_1'       => 'not-a-url',
            'video_url_2'       => 'also-not-a-url',
            'video_url_3'       => 'still-not-a-url',
            'notes'             => null,
            'payment_intent_id' => null,
        ];

    $request    = new StoreAnalysisRequest;
    $rules      = $request->rules();
    $messages   = $request->messages();
    $attributes = $request->attributes();

    $validator           = Validator::make($payload, $rules, $messages, $attributes);
    $errors              = $validator->errors();
    $emailError          = $errors->first('email');
    $tiktokUsernameError = $errors->first('tiktok_username');
    $bioError            = $errors->first('bio');
    $aspiringNicheError  = $errors->first('aspiring_niche');
    $videoUrl1Error      = $errors->first('video_url_1');
    $videoUrl2Error      = $errors->first('video_url_2');
    $videoUrl3Error      = $errors->first('video_url_3');
    $hasNotesError       = $errors->has('notes');

    $fails             = $validator->fails();
    $emailExpected     = trans('form.validation.email', ['attribute' => $attributes['email']                                ]);
    $tiktokExpected    = trans('form.validation.max',   ['attribute' => $attributes['tiktok_username'],     'max' => 255    ]);
    $bioExpected       = trans('form.validation.max',   ['attribute' => $attributes['bio'],                 'max' => 5000   ]);
    $nicheExpected     = trans('form.validation.max',   ['attribute' => $attributes['aspiring_niche'],      'max' => 255    ]);
    $videoUrl1Expected = trans('form.validation.url',   ['attribute' => $attributes['video_url_1']                          ]);
    $videoUrl2Expected = trans('form.validation.url',   ['attribute' => $attributes['video_url_2']                          ]);
    $videoUrl3Expected = trans('form.validation.url',   ['attribute' => $attributes['video_url_3']                          ]);

    expect($fails)
        ->toBeTrue()
        ->and($emailError)
        ->toBe($emailExpected)
        ->and($tiktokUsernameError)
        ->toBe($tiktokExpected)
        ->and($bioError)
        ->toBe($bioExpected)
        ->and($aspiringNicheError)
        ->toBe($nicheExpected)
        ->and($videoUrl1Error)
        ->toBe($videoUrl1Expected)
        ->and($videoUrl2Error)
        ->toBe($videoUrl2Expected)
        ->and($videoUrl3Error)
        ->toBe($videoUrl3Expected)
        ->and($hasNotesError)
        ->toBeFalse();
});

it('accepts nullable profile fields when omitted', function () {
    app()
        ->setLocale('en');

    $payload = [
            'email'             => 'jane@gmail.com',
            'aspiring_niche'    => 'Fitness',
            'notes'             => null,
            'payment_intent_id' => 'pi_test_init',
        ];

    $storeRequest = new StoreAnalysisRequest;
    $rules        = $storeRequest->rules();
    $validator    = Validator::make($payload, $rules);
    $fails        = $validator->fails();

    expect($fails)
        ->toBeFalse();
});

it('validates max length for video_url_2 with expected error message', function () {
    app()->setLocale('en');

    $payload                = validPayload();
    $payload['video_url_2'] = 'https://example.com/'.Str::repeat('a', 2100);

    $request    = new StoreAnalysisRequest;
    $rules      = $request->rules();
    $messages   = $request->messages();
    $attributes = $request->attributes();

    $validator      = Validator::make($payload, $rules, $messages, $attributes);
    $errors         = $validator->errors();
    $videoUrl2Error = $errors->first('video_url_2');
    $fails          = $validator->fails();
    $expected       = trans('form.validation.max', ['attribute' => $attributes['video_url_2'], 'max' => 2048]);

    expect($fails)
        ->toBeTrue()
        ->and($videoUrl2Error)
        ->toBe($expected);
});

it('validates string rule and expected error messages', function () {
    app()->setLocale('en');

    $payload                    = validPayload();
    $payload['email']           = ['not', 'a', 'string'];
    $payload['tiktok_username'] = ['not', 'a', 'string'];
    $payload['bio']             = ['not', 'a', 'string'];
    $payload['aspiring_niche']  = ['not', 'a', 'string'];
    $payload['notes']           = ['not', 'a', 'string'];

    $request    = new StoreAnalysisRequest;
    $rules      = $request->rules();
    $messages   = $request->messages();
    $attributes = $request->attributes();

    $validator           = Validator::make($payload, $rules, $messages, $attributes);
    $errors              = $validator->errors();
    $emailError          = $errors->first('email');
    $tiktokUsernameError = $errors->first('tiktok_username');
    $bioError            = $errors->first('bio');
    $aspiringNicheError  = $errors->first('aspiring_niche');
    $notesError          = $errors->first('notes');

    $fails          = $validator->fails();
    $emailExpected  = trans('form.validation.string', ['attribute' => $attributes['email']             ]);
    $tiktokExpected = trans('form.validation.string', ['attribute' => $attributes['tiktok_username']   ]);
    $bioExpected    = trans('form.validation.string', ['attribute' => $attributes['bio']               ]);
    $nicheExpected  = trans('form.validation.string', ['attribute' => $attributes['aspiring_niche']    ]);
    $notesExpected  = trans('form.validation.string', ['attribute' => $attributes['notes']             ]);

    expect($fails)
        ->toBeTrue()
        ->and($emailError)
        ->toBe($emailExpected)
        ->and($tiktokUsernameError)
        ->toBe($tiktokExpected)
        ->and($bioError)
        ->toBe($bioExpected)
        ->and($aspiringNicheError)
        ->toBe($nicheExpected)
        ->and($notesError)
        ->toBe($notesExpected);
});

it('sanitizes potentially unsafe fields before validation', function () {
    $request       = new StoreAnalysisRequest;
    $unsafePayload = [
        'email'             => '  <b>jane@example.com</b>  ',
        'tiktok_username'   => '  <script>alert(1)</script>@jane  ',
        'bio'               => '  <p>Hello <strong>world</strong></p>  ',
        'aspiring_niche'    => '  <img src=x onerror=alert(1)>Fitness  ',
        'video_url_1'       => '  https://example.com/video-1  ',
        'video_url_2'       => '  https://example.com/video-2  ',
        'video_url_3'       => '  https://example.com/video-3  ',
        'notes'             => '  <iframe src="javascript:alert(1)"></iframe>note  ',
        'payment_intent_id' => '  <b>pi_test_init</b>  ',
    ];

    $request->merge($unsafePayload);

    $method = new ReflectionMethod(StoreAnalysisRequest::class, 'prepareForValidation');
    $method->setAccessible(true);
    $method->invoke($request);

    $email           = $request->input('email');
    $tiktokUsername  = $request->input('tiktok_username');
    $bio             = $request->input('bio');
    $aspiringNiche   = $request->input('aspiring_niche');
    $videoUrl1       = $request->input('video_url_1');
    $videoUrl2       = $request->input('video_url_2');
    $videoUrl3       = $request->input('video_url_3');
    $notes           = $request->input('notes');
    $paymentIntentId = $request->input('payment_intent_id');

    expect($email)
        ->toBe('jane@example.com')
        ->and($tiktokUsername)
        ->toBe('alert(1)@jane')
        ->and($bio)
        ->toBe('Hello world')
        ->and($aspiringNiche)
        ->toBe('Fitness')
        ->and($videoUrl1)
        ->toBe('https://example.com/video-1')
        ->and($videoUrl2)
        ->toBe('https://example.com/video-2')
        ->and($videoUrl3)
        ->toBe('https://example.com/video-3')
        ->and($notes)
        ->toBe('note')
        ->and($paymentIntentId)
        ->toBe('pi_test_init');
});

it('converts non-string values to null during sanitization', function () {
    $request          = new StoreAnalysisRequest;
    $nonStringPayload = [
        'email'                 => 123,
        'tiktok_username'       => ['array'],
        'bio'                   => ['array'],
        'aspiring_niche'        => false,
        'video_url_1'           => 1.2,
        'video_url_2'           => ['array'],
        'video_url_3'           => true,
        'notes'                 => null,
        'payment_intent_id'     => ['array'],
    ];

    $request->merge($nonStringPayload);

    $method = new ReflectionMethod(StoreAnalysisRequest::class, 'prepareForValidation');
    $method->setAccessible(true);
    $method->invoke($request);

    $email           = $request->input('email');
    $tiktokUsername  = $request->input('tiktok_username');
    $bio             = $request->input('bio');
    $aspiringNiche   = $request->input('aspiring_niche');
    $videoUrl1       = $request->input('video_url_1');
    $videoUrl2       = $request->input('video_url_2');
    $videoUrl3       = $request->input('video_url_3');
    $notes           = $request->input('notes');
    $paymentIntentId = $request->input('payment_intent_id');

    expect($email)
        ->toBeNull()
        ->and($tiktokUsername)
        ->toBeNull()
        ->and($bio)
        ->toBeNull()
        ->and($aspiringNiche)
        ->toBeNull()
        ->and($videoUrl1)
        ->toBeNull()
        ->and($videoUrl2)
        ->toBeNull()
        ->and($videoUrl3)
        ->toBeNull()
        ->and($notes)
        ->toBeNull()
        ->and($paymentIntentId)
        ->toBeNull();
});

function validPayload(): array
{
    $bio = Str::repeat('a', 100);

    return [
        'email'             => 'jane@example.com',
        'tiktok_username'   => '@janedoe',
        'bio'               => $bio,
        'aspiring_niche'    => 'Fitness',
        'video_url_1'       => 'https://example.com/video-1',
        'video_url_2'       => 'https://example.com/video-2',
        'video_url_3'       => 'https://example.com/video-3',
        'notes'             => 'Optional notes',
        'payment_intent_id' => 'pi_test_init',
    ];
}
