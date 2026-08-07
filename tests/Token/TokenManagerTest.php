<?php

declare(strict_types=1);

use Pin\Support\Facades\Token;
use Pin\Token\Drivers\AesDriver;
use Pin\Token\TokenFactory;

it('manages token drivers', function () {
    $driver = Token::driver();

    expect($driver)->toBeInstanceOf(TokenFactory::class)
        ->and(Token::driver())->toBe($driver);

    expect(Token::driver('jwt'))->toBeInstanceOf(TokenFactory::class)
        ->and(Token::driver('session'))->toBeInstanceOf(TokenFactory::class);

    // extend without config
    Token::extend(
        'auth-token-without-config',
        fn () => new TokenFactory(new AesDriver()));

    expect(Token::driver('auth-token-without-config'))
        ->toBeInstanceOf(TokenFactory::class);

    // extend with config
    config(['pin.token.drivers.auth-token-with-config' => ['driver' => 'auth-token-with-config']]);
    Token::extend('auth-token-with-config', fn () => new TokenFactory(new AesDriver()));

    expect(Token::driver('auth-token-with-config'))
        ->toBeInstanceOf(TokenFactory::class);

    // __call encode/decode shortcut
    expect(Token::decode(Token::encode(['uid' => 1]))->uid)->toBe(1);
});

it('throws exception when driver is not defined', function () {
    $this->expectExceptionMessage('Token driver [s] is not defined.');
    Token::driver('s');
});

it('throws exception when driver is not supported', function () {
    config(['pin.token.drivers.s' => []]);

    $this->expectExceptionMessage('Token driver [s] is not supported.');
    Token::driver('s');
});
