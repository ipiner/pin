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

it('encodes plain value in non production', function () {
    $result = $this->middleware->transform('password', 'plain:123456');
    expect($result)->toBe(Password::encode('123456'));
});
