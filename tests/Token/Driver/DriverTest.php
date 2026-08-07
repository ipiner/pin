<?php

declare(strict_types=1);

use Pin\Token\Drivers\AesDriver;
use Pin\Token\Token;

it('validates token expiration fields', function () {
    $driver = Pin\Support\Facades\Token::driver();

    $token = $driver->decode($driver->encode(['uid' => 1]));
    expect($token->uid)->toBe(1);
    expect(isset($token->exp))->toBeFalse();

    $token = $driver->decode($driver->encode([], 60));
    expect(isset($token->exp))->toBeTrue();
});

it('checks token expiration logic', function () {
    $driver = new AesDriver();
    $o = $this->invoker($driver);

    $token = new Token([], '');
    expect($o->isExpired($token))->toBeFalse();

    // exp not expired
    $token = new Token(['exp' => time() + 100], '');
    expect($o->isExpired($token))->toBeFalse();

    // exp expired
    $token = new Token(['exp' => time() - 100], '');
    expect($o->isExpired($token))->toBeTrue();

    // expires not expired
    $token = new Token(['iat' => time(), 'expires' => 100], '');
    expect($o->isExpired($token))->toBeFalse();

    // expires expired
    $token = new Token(['iat' => time(), 'expires' => -100], '');
    expect($o->isExpired($token))->toBeTrue();
});
