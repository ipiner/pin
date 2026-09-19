<?php

declare(strict_types=1);

use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\CacheManager;
use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Pin\Support\Facades\Token;
use Pin\Token\Drivers\AesDriver;
use Pin\Token\TokenFactory;
use Pin\Token\TokenManager;

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

it('uses the cache binding from the manager application', function () {
    Cache::getFacadeRoot();

    $cache = new Repository(new ArrayStore());
    $manager = Mockery::mock(CacheManager::class, [$this->app])->makePartial();
    $manager->shouldReceive('store')->once()->with('token-tests')->andReturn($cache);
    $this->app->instance('cache', $manager);

    $factory = (new TokenManager($this->app))->build([
        'driver' => 'session',
        'cacheStore' => 'token-tests',
        'refresh_before' => 0,
    ]);
    $token = $factory->decode($factory->encode(['uid' => 1], 60));

    expect($token->uid)->toBe(1)
        ->and($cache->get($token->jti))->toBe($token->exp);
});
