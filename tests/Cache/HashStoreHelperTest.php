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

it('preserves an existing expiry when writing another field', function () {
    HashCache::put('users:1', 1, 90);

    expect(HashCache::ttl('users'))->toBeBetween(89, 90);

    HashCache::put('users:2', 2, 3600);

    expect(HashCache::ttl('users'))->toBeBetween(89, 90);
});

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
    ?int $ttl,
    int $expected,
) {
    $store = $this->invoker(HashCache::store()->getStore());

    expect($store->getTTL($ttl))->toBe($expected);

})->with([
    'default ttl' => [null, 604800],
    'forever' => [0, 0],
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

it('reads a single field through many', function () {
    HashCache::forever('users:1', 1);

    expect(HashCache::many(['users:1']))->toBe([1])
        ->and(HashCache::many(['users:1', 'users:1']))->toBe([1, 1]);
});

it('returns keyed values and defaults through the cache repository', function () {
    HashCache::forever('users:1', false);
    $repository = HashCache::store();

    expect($repository->many(['users:1', 'users:missing']))->toBe([
        'users:1' => false,
        'users:missing' => null,
    ])->and($repository->getMultiple(['users:1', 'users:missing'], 'default'))->toBe([
        'users:1' => false,
        'users:missing' => 'default',
    ]);
});

it('handles empty batches', function () {
    $store = HashCache::getStore();

    expect($store->putMany([]))->toBeTrue()
        ->and($store->many([]))->toBe([])
        ->and($store->forget([]))->toBeFalse();
});

it('forgets multiple hashes', function () {
    HashCache::forever('users:1', 1);
    HashCache::forever('teams:1', 2);

    expect(HashCache::getStore()->forget(['users', 'teams']))->toBeTrue()
        ->and(HashCache::get('users:1'))->toBeNull()
        ->and(HashCache::get('teams:1'))->toBeNull();
});

it('rejects mixed hashes before writing any values', function () {
    expect(fn () => HashCache::getStore()->putMany(['users:1' => 1, 'teams:1' => 2], 60))
        ->toThrow(InvalidArgumentException::class);

    expect(HashCache::getAll('users'))->toBe([])
        ->and(HashCache::getAll('teams'))->toBe([]);
});

it('sets the ttl on the full hash key', function () {
    expect(HashCache::put('tests:users:1', 1, 90))->toBeTrue()
        ->and(HashCache::ttl('tests:users'))->toBeBetween(89, 90);
});

it('removes the hash ttl when writing forever', function () {
    HashCache::forever('users:1', 1);
    HashCache::getDriver()->expire('users', 60);

    expect(HashCache::forever('users:1', 2))->toBeTrue()
        ->and(HashCache::ttl('users'))->toBe(-1)
        ->and(HashCache::get('users:1'))->toBe(2);
});

it('refreshes an existing hash ttl', function () {
    HashCache::forever('tests:users:1', 1);
    HashCache::getDriver()->expire('tests:users', 60);

    expect(HashCache::touch('tests:users:', 300))->toBeTrue()
        ->and(HashCache::ttl('tests:users'))->toBeBetween(299, 300)
        ->and(HashCache::touch('missing:', 300))->toBeFalse();
});
