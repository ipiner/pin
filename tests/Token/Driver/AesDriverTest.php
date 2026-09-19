<?php

declare(strict_types=1);

use Pin\Crypt\CryptException;
use Pin\Exceptions\Exception;
use Pin\Support\Facades\Aes;
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

it('rejects decrypted data that is not a valid payload', function (
    string $driver,
    string $payload,
) {
    $raw = Aes::encrypt($payload);

    try {
        Token::driver($driver)->decode($raw);
        $this->fail('Expected an invalid token exception.');
    } catch (TokenInvalidException $exception) {
        expect($exception->token->raw)->toBe($raw)
            ->and($exception->token->payload->toArray())->toBe([]);
    }
})->with(['default', 'session'])->with(['{', 'null', 'false', '123', '"text"']);

it('preserves decryption and JSON errors', function (bool $invalidJson, string $previous) {
    $raw = $invalidJson ? Aes::encrypt('{') : 'invalid';

    try {
        Token::decode($raw);
        $this->fail('Expected an invalid token exception.');
    } catch (TokenInvalidException $exception) {
        expect($exception->getPrevious())->toBeInstanceOf($previous)
            ->and($exception->token->raw)->toBe($raw);
    }
})->with([
    'decryption' => [false, CryptException::class],
    'JSON' => [true, Exception::class],
]);
