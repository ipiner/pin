<?php

declare(strict_types=1);

use Pin\Support\Facades\Token;
use Pin\Token\Exceptions\TokenExpiredException;
use Pin\Token\Exceptions\TokenInvalidException;

it('decodes jwt token', function () {
    $driver = Token::driver('jwt');
    $raw = $driver->encode(['uid' => 1], 60);
    expect($driver->decode($raw)->uid)->toBe(1);
});

it('throws expired exception when jwt token is expired', function () {
    $driver = Token::driver('jwt');
    $raw = $driver->encode([], -60);
    $this->expectException(TokenExpiredException::class);
    $driver->decode($raw);
});

it('throws invalid exception when jwt token is invalid', function () {
    $driver = Token::driver('jwt');
    $this->expectException(TokenInvalidException::class);
    $driver->decode('invalid');
});
