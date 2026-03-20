<?php

use App\Models\User;
use Illuminate\Support\Facades\Gate;

it('allows viewHorizon when user email is in horizon.allowed_emails', function (): void {
    config(['horizon.allowed_emails' => ['ops@goviral.test', 'admin@goviral.test']]);
    $user = User::factory()->create(['email' => 'ops@goviral.test']);

    expect(Gate::forUser($user)->allows('viewHorizon'))->toBeTrue();
});

it('denies viewHorizon when user email is not in horizon.allowed_emails', function (): void {
    config(['horizon.allowed_emails' => ['ops@goviral.test']]);
    $user = User::factory()->create(['email' => 'other@goviral.test']);

    expect(Gate::forUser($user)->allows('viewHorizon'))->toBeFalse();
});

it('denies viewHorizon when allowed list is empty', function (): void {
    config(['horizon.allowed_emails' => []]);
    $user = User::factory()->create(['email' => 'ops@goviral.test']);

    expect(Gate::forUser($user)->allows('viewHorizon'))->toBeFalse();
});

it('denies viewHorizon for unauthenticated user', function (): void {
    config(['horizon.allowed_emails' => ['ops@goviral.test']]);

    expect(Gate::allows('viewHorizon'))->toBeFalse();
});
