<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use Pin\Auth\Auth;
use Pin\Auth\TokenResolver;
use Pin\Errors\Errors;
use Pin\Token\Exceptions\TokenInvalidException;

pest()->beforeEach(function () {
    $this->resolver = new TokenResolver();
});

it('resolves token to uid', function (?string $requestToken, ?int $expectedUid) {
    $token = is_int($expectedUid)
        ? Auth::token()->encode(['uid' => $expectedUid], 10)
        : $requestToken;

    $this->resolver->resolve($token);

    expect($this->resolver->getUid())->toBe($expectedUid);
})->with([
    'no token provided' => [null, null],
    'sanctum token' => ['1|'.Str::random(), null],
    'valid token' => ['1', 1],
]);

it('throws token invalid exception for invalid token', function () {
    expect(fn () => $this->resolver->resolve('xxx'))->toThrow(
        TokenInvalidException::class,
        Errors::TokenInvalid->message()
    );
});

it('resolves request token', function (
    ?string $bearer,
    ?string $header,
    ?string $query,
    ?string $expected,
) {
    app()->request->headers->set(
        'Authorization',
        $bearer ? 'Bearer '.$bearer : null,
    );

    app()->request->headers->set('token', $header);
    app()->request->query->set('token', $query);

    expect($this->resolver->getRequestToken())
        ->toBe($expected);

})->with([
    'bearer token' => ['bearer', null, null, 'bearer'],
    'header token' => [null, 'header', null, 'header'],
    'query token' => [null, null, 'query', 'query'],
]);

it('forgets resolved token', function () {
    $this->resolver->resolve(
        Auth::token()->encode([], 10),
    );

    expect($this->resolver->getResolvedToken())
        ->not()->toBeNull();

    $this->resolver->forgetToken();

    expect($this->resolver->getResolvedToken())
        ->toBeNull();
});
