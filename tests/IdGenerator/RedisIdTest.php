<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;
use Pin\IdGenerator\RedisId;
use Pin\Testing\Concerns\InteractsWithRedis;

uses(InteractsWithRedis::class);

it('generates redis ids', function () {
    expect(app('pin.id.redis')->generate())->toBe(1)
        ->and(app('pin.id.redis')->generate())->toBe(2);

    // lock
    $generator = new RedisId([
        'name' => 'default',
        'use_lock' => true,
    ]);

    expect($generator->generate())->toBe(3);
});

it('generates multiple redis ids', function () {
    expect(app('pin.id.redis')->generate(5))
        ->toBe([1, 2, 3, 4, 5])

        ->and(app('pin.id.redis')->generate(3))
        ->toBe([6, 7, 8]);

    $generator = new RedisId([
        'name' => 'testing',
        'use_lock' => false,
    ]);

    expect($generator->generate(5))
        ->toBe([1, 2, 3, 4, 5])

        ->and($generator->generate(3))
        ->toBe([6, 7, 8]);
});

it('skips cache access for empty batches', function (int $count, bool $useLock) {
    Cache::shouldReceive('store')->never();
    Cache::shouldReceive('lock')->never();

    $generator = new RedisId(['name' => 'empty', 'use_lock' => $useLock]);

    expect($generator->generate($count))->toBe([]);
})->with([0, -1])->with([false, true]);

it('uses Redis locks when the default cache store differs', function () {
    config(['cache.default' => 'array']);
    $generator = new RedisId(['name' => 'locked', 'use_lock' => true]);
    $lock = Cache::store('redis')->lock('redis-id-locked', 60);

    $result = $this->invoker($generator)->lock(function () use ($lock) {
        expect($lock->isLocked())->toBeTrue();

        return 42;
    });

    expect($result)->toBe(42)->and($lock->isLocked())->toBeFalse();
});

it('uses a different owner for each lock', function () {
    config(['cache.default' => 'redis']);
    $generator = new RedisId(['name' => 'owner', 'use_lock' => true]);
    $lock = $this->invoker(Cache::store('redis')->lock('redis-id-owner', 60));
    $generator = $this->invoker($generator);

    $first = $generator->lock(fn () => $lock->getCurrentOwner());
    $second = $generator->lock(fn () => $lock->getCurrentOwner());

    expect($first)->toBeString()->not->toBeEmpty()
        ->and($second)->toBeString()->not->toBe($first);
});
