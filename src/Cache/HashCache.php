<?php

declare(strict_types=1);

namespace Pin\Cache;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;

/**
 * @method static HashDriver getDriver()
 *
 * @mixin HashDriver
 * @mixin HashStore
 * @mixin Repository
 */
class HashCache
{
    /**
     * 代理到 Redis Hash Store
     */
    public function __call(string $method, array $arguments): mixed
    {
        return $this->store()->{$method}(...$arguments);
    }

    /**
     * 获取 Redis Hash 缓存实例
     */
    public function store(): Repository
    {
        return Cache::store('redis-hash');
    }
}
