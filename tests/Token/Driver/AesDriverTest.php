<?php

declare(strict_types=1);

use Pin\Support\Facades\Token;
use Pin\Token\Exceptions\TokenInvalidException;

it('encodes and decodes token', function () {
    $driver = Token::driver();
    $raw = $driver->encode(['uid' => 1], 60);

    expect($driver->decode($raw)->uid)->toBe(1);
});

it('throws exception when token is invalid', function () {
    $driver = Token::driver();
    $this->expectException(TokenInvalidException::class);

    $driver->decode('s');
});
