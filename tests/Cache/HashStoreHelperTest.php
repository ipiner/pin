<?php

declare(strict_types=1);

use Pin\Cache\RedisHashDriver;
use Pin\Support\Facades\HashCache;
use Pin\Testing\Concerns\InteractsWithRedis;

uses(InteractsWithRedis::class)->beforeEach(function () {
    $this->cleanRedis();
});

it('throws exception for unsupported methods', function (
    string $method,
    array $arguments,
) {
    HashCache::{$method}(...$arguments);

})->with([
    'decrement' => ['decrement', ['key']],
    'increment' => ['increment', ['key']],
    'flush' => ['flush', []],
])->throws(BadMethodCallException::class);

it('forwards calls to redis driver', function () {
    expect(HashCache::hIncrBy('key', 'field', 1))
        ->toBe(1);
});

it('deletes hash keys', function (
    array $data,
    string|array $key,
    bool $expected,
) {
    if ($data !== []) {
        HashCache::putMany($data);
    }

    expect(HashCache::del($key))->toBe($expected);

})->with([
    'missing hash' => [
        'data' => [],
        'key' => 'users',
        'expected' => false,
    ],

    'delete hash' => [
        'data' => [
            'users:1' => 1,
            'users:2' => 2,
        ],
        'key' => 'users',
        'expected' => true,
    ],

    'delete multiple hashes' => [
        'data' => [
            'users:1' => 1,
        ],
        'key' => ['users', 'foo'],
        'expected' => true,
    ],
]);

it('expires hash keys', function (
    int $ttl,
    bool $overwrite,
    bool $expected,
) {
    $store = $this->invoker(HashCache::store()->getStore());

    HashCache::set('users:1', '1');
    if ($ttl === 0) {
        // 模拟 $this->driver->ttl($key) !== -1
        expect($store->expire('users', 86400, $overwrite))->toBe($expected);
    }

    expect($store->expire('users', $ttl, $overwrite))->toBe($expected);

})->with([
    'skip ttl update' => [1000, false, false],
    'set ttl' => [90, true, true],
    'refresh ttl' => [0, true, true],
]);

it('stores values forever', function () {
    HashCache::forever('users:1', 1);

    expect(HashCache::get('users:1'))->toBe(1);
});

it('forgets hash values', function (
    array $initial,
    string $forget,
    bool $expected,
    array $remaining,
) {
    if ($initial !== []) {
        HashCache::putMany($initial);
    }

    expect(HashCache::forget($forget))->toBe($expected);

    foreach ($remaining as $key => $value) {
        expect(HashCache::get($key))->toBe($value);
    }

})->with([
    'missing field' => [
        'initial' => [],
        'forget' => 'users:1',
        'expected' => false,
        'remaining' => [],
    ],

    'forget field' => [
        'initial' => [
            'users:1' => 1,
            'users:2' => 2,
        ],
        'forget' => 'users:1',
        'expected' => true,
        'remaining' => [
            'users:1' => null,
            'users:2' => 2,
        ],
    ],

    'forget hash' => [
        'initial' => [
            'users:1' => 1,
            'users:2' => 2,
        ],
        'forget' => 'users',
        'expected' => true,
        'remaining' => [
            'users:1' => null,
            'users:2' => null,
        ],
    ],
]);

it('gets all hash values', function (
    array $data,
    string $key,
    array $expected,
) {
    if ($data !== []) {
        HashCache::putMany($data);
    }

    expect(HashCache::getAll($key))->toBe($expected);

})->with([
    'empty users hash' => [
        [],
        'users',
        [],
    ],

    'users hash' => [
        [
            'users:1' => 1,
            'users:2' => 2,
        ],
        'users',
        [
            1 => 1,
            2 => 2,
        ],
    ],

    'prefixed users hash' => [
        [
            'tests:users:1' => 1,
            'tests:users:2' => 2,
        ],
        'tests:users',
        [
            1 => 1,
            2 => 2,
        ],
    ],
]);

it('returns redis hash driver', function () {
    expect(HashCache::getDriver())->toBeInstanceOf(RedisHashDriver::class);
});

it('returns empty prefix', function () {
    expect(HashCache::getPrefix())->toBe('');
});

it('resolves ttl', function (
    int $ttl,
    int $expected,
) {
    $store = $this->invoker(HashCache::store()->getStore());

    expect($store->getTTL($ttl))->toBe($expected);

})->with([
    'default ttl' => [0, 604800],
    'custom ttl' => [10, 10],
]);

it('gets many values', function () {
    HashCache::putMany([
        'users:1' => 1,
        'users:2' => 2,
    ]);
    HashCache::put('users:3', 3, 3600);

    expect(HashCache::many([
        'users:2',
        'users:1',
        'users:3',
        'users:4',
    ]))->toBe([
        2,
        1,
        3,
        null,
    ]);
});

it('touches hash ttl', function () {
    HashCache::forever('users:1', 1);

    expect(HashCache::touch('users:', 1000))->toBeTrue();
});
