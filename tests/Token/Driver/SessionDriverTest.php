<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Pin\Testing\Concerns\InteractsWithRedis;
use Pin\Token\Drivers\SessionDriver;
use Pin\Token\Exceptions\TokenExpiredException;
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
