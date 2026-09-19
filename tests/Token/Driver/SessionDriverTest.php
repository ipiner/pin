<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Pin\Testing\Concerns\InteractsWithRedis;
use Pin\Token\Drivers\SessionDriver;
use Pin\Token\Exceptions\TokenExpiredException;
use Pin\Token\Exceptions\TokenInvalidException;
use Pin\Token\Exceptions\TokenMissingException;
use Pin\Token\Token;
use Pin\Token\TokenPayload;

uses(InteractsWithRedis::class);

it('decodes session token', function () {
    $driver = Pin\Support\Facades\Token::driver('session');
    $raw = $driver->encode(['uid' => 1], 60);
    expect($driver->decode($raw)->uid)->toBe(1);
});

it('throws expired exception when session token is expired by cache', function () {
    $key = Str::random();
    $driver = Pin\Support\Facades\Token::driver('session');

    $raw = $driver->encode(['jti' => $key], 60);
    Cache::put($key, time() - 60, 60);

    $this->expectException(TokenExpiredException::class);
    $driver->decode($raw);
});

it('throws expired exception when session token exceeds max age', function () {
    $driver = Pin\Support\Facades\Token::driver('session');
    $raw = $driver->encode([], 60);
    $this->travel(1)->year();

    $this->expectException(TokenExpiredException::class);
    $driver->decode($raw);
});

it('throws missing exception when session token is not found', function () {
    $key = Str::random();
    $driver = Pin\Support\Facades\Token::driver('session');

    $raw = $driver->encode(['jti' => $key], 60);
    Cache::forget($key);

    $this->expectException(TokenMissingException::class);
    $driver->decode($raw);
});

it('forgets session token', function () {
    $driver = Pin\Support\Facades\Token::driver('session');

    expect($driver->forget(null))->toBeFalse();

    $key = Str::random();
    $payload = new TokenPayload(['jti' => $key]);

    $driver->encode($payload, 60);
    expect(Cache::has($key))->toBeTrue();

    $driver->forget($key);
    expect(Cache::has($key))->toBeFalse();

    $driver->encode($payload, 60);
    expect(Cache::has($key))->toBeTrue();

    $driver->forget(new Token($payload, ''));
    expect(Cache::has($key))->toBeFalse();
});

it('refreshes session token based on config', function () {
    $key = Str::random();
    $driver = new SessionDriver(Cache::store(), ['refresh_before' => 0]);

    $raw = $driver->encode(new TokenPayload(['jti' => $key]), 60);
    $token = $driver->decode($raw);
    expect($this->invoker($driver)->refresh($token))->toBeFalse();

    $driver = new SessionDriver(Cache::store(), []);
    expect($this->invoker($driver)->refresh($token))->toBeTrue();
});

it('fills missing session settings regardless of the number of supplied options', function () {
    $this->freezeTime();
    $driver = new SessionDriver(Cache::store('array'), [
        'driver' => 'session',
        'expires' => 120,
        'refresh_before' => 0,
    ]);
    $token = $driver->decode($driver->encode(new TokenPayload(['uid' => 1])));

    expect($token->uid)->toBe(1)
        ->and($token->expires)->toBe(120)
        ->and($token->exp)->toBe(now()->getTimestamp() + 120)
        ->and($token->jti)->toStartWith(config('pin.token.drivers.session.cache_prefix'));
});

it('rejects session payloads without required fields', function (string $field) {
    $payload = [
        'iat' => now()->getTimestamp(),
        'expires' => 60,
        'jti' => 'invalid-session',
    ];
    unset($payload[$field]);

    $raw = Pin\Support\Facades\Token::encode($payload);
    $driver = new SessionDriver(Cache::store('array'), []);

    expect(fn () => $driver->decode($raw))->toThrow(TokenInvalidException::class);
})->with(['iat', 'expires', 'jti']);

it('renews the cached expiration without changing the issued token', function () {
    $this->freezeTime();
    $cache = Cache::store('array');
    $driver = new SessionDriver($cache, ['refresh_before' => 30]);
    $raw = $driver->encode(new TokenPayload(['uid' => 1]), 60);
    $original = $driver->decode($raw);

    $this->travel(40)->seconds();
    $renewed = $driver->decode($raw);

    expect($renewed->raw)->toBe($raw)
        ->and($renewed->jti)->toBe($original->jti)
        ->and($renewed->iat)->toBe($original->iat)
        ->and($renewed->exp)->toBe($original->exp + 40)
        ->and($cache->get($renewed->jti))->toBe($renewed->exp);

    $driver->forget($renewed);

    expect(fn () => $driver->decode($raw))->toThrow(TokenMissingException::class);
});
