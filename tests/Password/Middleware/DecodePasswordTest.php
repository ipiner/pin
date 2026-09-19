<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use Pin\Password\Middleware\DecodePassword;
use Pin\Support\Facades\Password;

beforeEach(function () {
    $this->middleware = $this->invoker(new DecodePassword());
});

it('decodes password field when value is encoded', function () {
    $password = Str::random();

    expect(
        $this->middleware->transform('password', Password::encodeToRequest($password))
    )
        ->toBe(Password::encode($password));
});

it('decodes password field when value is encoded is empty', function () {
    expect(
        $this->middleware->transform('password', Password::encodeToRequest(''))
    )
        ->toBe('');
});

it('encodes plain value in non production', function () {
    $result = $this->middleware->transform('password', 'plain:123456');
    expect($result)->toBe(Password::encode('123456'));
});

it('decodes empty and zero plain values', function (string $plain) {
    $value = $this->middleware->transform('password', 'plain:'.$plain);

    expect($value)->toBe($plain === '' ? '' : Password::encode($plain))
        ->and($value)->toBe(
            $this->middleware->transform('password', Password::encodeToRequest($plain))
        );
})->with(['', '0']);
