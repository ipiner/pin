<?php

declare(strict_types=1);

use Illuminate\Http\Request;
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
        $bearer !== null ? 'Bearer '.$bearer : null,
    );

    app()->request->headers->set('token', $header);
    app()->request->query->set('token', $query);

    expect($this->resolver->getRequestToken())
        ->toBe($expected);

})->with([
    'bearer token' => ['bearer', null, null, 'bearer'],
    'header token' => [null, 'header', null, 'header'],
    'query token' => [null, null, 'query', 'query'],
    'bearer takes priority' => ['bearer', 'header', 'query', 'bearer'],
    'header takes priority over query' => [null, 'header', 'query', 'header'],
    'surrounding whitespace' => [null, '  header  ', 'query', 'header'],
    'blank header falls back to query' => [null, '  ', 'query', 'query'],
    'zero bearer falls back to header' => ['0', 'header', 'query', 'header'],
    'zero header falls back to query' => [null, '0', 'query', 'query'],
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

it('clears a previous identity when resolving an ignored token', function (?string $token) {
    $this->resolver->resolve(Auth::token()->encode(['uid' => 1], 60));

    expect($this->resolver->resolve($token))->toBeNull()
        ->and($this->resolver->getResolvedToken())->toBeNull()
        ->and($this->resolver->getUid())->toBeNull();
})->with([null, '', '0', '  ', '1|sanctum-token']);

it('clears a previous identity when decoding fails', function () {
    $this->resolver->resolve(Auth::token()->encode(['uid' => 1], 60));

    expect(fn () => $this->resolver->resolve('invalid'))->toThrow(TokenInvalidException::class)
        ->and($this->resolver->getResolvedToken())->toBeNull()
        ->and($this->resolver->getUid())->toBeNull();
});

it('ignores non-string query values', function (mixed $token) {
    $request = Request::create('/');
    $request->query->set('token', $token);

    expect((new TokenResolver($request))->getRequestToken())->toBeNull();
})->with([[['nested']], [12], [false]]);

it('reads the user ID from the token', function (?int $uid, ?int $expected) {
    $this->resolver->resolve(Auth::token()->encode(['uid' => $uid], 60));

    expect($this->resolver->getUid())->toBe($expected);
})->with([
    'integer' => [12, 12],
    'zero' => [0, 0],
    'negative' => [-1, -1],
    'missing' => [null, null],
]);

it('identifies Sanctum tokens', function (string $token, bool $expected) {
    expect($this->resolver->isSanctumToken($token))->toBe($expected);
})->with([
    ['1|secret', true],
    ['123|custom_prefix-secret', true],
    ['1invalid|secret', true],
    ['1|', true],
    ['0|secret', false],
    ['1|secret|extra', true],
]);

it('resets parsed state when changing requests without revoking the previous token', function () {
    $raw = Auth::token()->encode(['uid' => 1], 60);
    $this->resolver->resolve($raw);
    $request = Request::create('/?token=next');

    expect($this->resolver->setRequest($request))->toBe($this->resolver)
        ->and($this->resolver->getRequestToken())->toBe('next')
        ->and($this->resolver->getResolvedToken())->toBeNull()
        ->and(Auth::token()->decode($raw)->uid)->toBe(1);
});
