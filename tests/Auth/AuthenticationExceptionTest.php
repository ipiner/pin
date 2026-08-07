<?php

declare(strict_types=1);

use Pin\Auth\AuthenticationException;
use Pin\Errors\Errors;

it('returns 401 status code', function () {
    expect(new AuthenticationException()->getStatusCode())
        ->toBe(401);
});

it('resolves message', function (string $message, string $expected) {
    expect(new AuthenticationException($message)->getMessage())
        ->toBe($expected);

})->with([
    'default message' => ['', '请登录'],
    'custom message' => ['login', 'login'],
]);

it('resolves code', function (int $code, int $expected) {
    expect(new AuthenticationException(code: $code)->getCode())
        ->toBe($expected);

})->with([
    'default code' => [0, 401],
    'custom code' => [403, 403],
]);

it('maps auth error codes', function (int $code, int $expected) {
    expect(new AuthenticationException(code: $code)->getCode())
        ->toBe($expected);

})->with([
    'token expired' => [
        Errors::TokenExpired->code(),
        Errors::AuthTokenExpired->code(),
    ],

    'token invalid' => [
        Errors::TokenInvalid->code(),
        Errors::AuthTokenInvalid->code(),
    ],

    'token missing' => [
        Errors::TokenMissing->code(),
        Errors::AuthTokenMissing->code(),
    ],

    'default code' => [0, 401],
]);
