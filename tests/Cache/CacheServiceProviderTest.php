<?php

declare(strict_types=1);

use Illuminate\Redis\Connections\PhpRedisConnection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Pin\Cache\RedisStore;

it('uses the configuration of each hash cache store', function () {
    config(['cache.stores.custom-hash' => [
        'driver' => 'redis-hash',
        'connection' => 'default',
        'ttl' => 42,
        'events' => false,
    ]]);
    Redis::shouldReceive('connection')->once()->with('default')
        ->andReturn(Mockery::mock(PhpRedisConnection::class));

    $repository = Cache::store('custom-hash');

    expect($repository->getStore())->toBeInstanceOf(RedisStore::class)
        ->and($this->invoker($repository->getStore())->getTTL())->toBe(42)
        ->and($this->invoker($repository)->config['store'])->toBe('custom-hash')
        ->and($this->invoker($repository)->events)->toBeNull();
});
