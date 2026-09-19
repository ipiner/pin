<?php

declare(strict_types=1);

use App\Factories\UserFactory;
use Illuminate\Support\Facades\Cache;
use Pin\Auth\Auth;
use Pin\Auth\Guard;
use Pin\Auth\TokenResolver;
use Pin\Tests\InteractsWithDatabase;

uses(InteractsWithDatabase::class);

it('registers custom auth guard', function () {
    expect(auth()->guard(Guard::NAME))->toBeInstanceOf(Guard::class);
});

it('uses the token key configured for each guard', function () {
    app()->request->server->set('argv', null);
    config([
        'auth.guards.pin.token_key' => 'pin-token',
        'auth.guards.custom' => ['driver' => 'pin', 'provider' => 'pin', 'token_key' => 'api-key'],
    ]);
    $user = UserFactory::new()->create();
    $raw = Auth::token()->encode(['uid' => $user->id], 60);
    app()->request->headers->set('api-key', $raw);
    $guard = auth('custom');

    expect($guard->id())->toBe($user->id)
        ->and($guard->validate(['api-key' => $raw]))->toBeTrue()
        ->and($guard->validate(['pin-token' => $raw]))->toBeFalse()
        ->and(auth('pin')->user())->toBeNull();
});

it('resolves custom token resolvers through the container', function () {
    app()->request->server->set('argv', null);
    $user = UserFactory::new()->create();
    $resolver = Mockery::mock(TokenResolver::class)->makePartial();
    $resolver->shouldReceive('getRequestToken')->once()->andReturn(Auth::token()->encode(['uid' => $user->id], 60));
    app()->bind(TokenResolver::class, fn () => $resolver);

    expect(auth('pin')->id())->toBe($user->id);
});

it('preserves the default authentication token prefix', function () {
    $raw = Auth::token()->encode([], 60);

    expect(Auth::token()->decode($raw)->jti)->toStartWith('auth-token:');
});

it('applies authentication token options while retaining session defaults', function () {
    config([
        'cache.stores.auth-tests' => ['driver' => 'array'],
        'pin.token.drivers.auth-token' => [
            'cacheStore' => 'auth-tests',
            'cache_prefix' => 'custom-auth:',
            'expires' => 30,
            'refresh_before' => 0,
        ],
    ]);
    $raw = Auth::token()->encode(['uid' => 1]);
    $token = Auth::token()->decode($raw);

    expect($token->jti)->toStartWith('custom-auth:')
        ->and($token->exp - $token->iat)->toBe(30)
        ->and(Cache::store('auth-tests')->has($token->jti))->toBeTrue()
        ->and(Cache::store()->has($token->jti))->toBeFalse();
});
