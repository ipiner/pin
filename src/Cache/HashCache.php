<?php

declare(strict_types=1);

namespace Pin\Cache;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;

/**
 * Hash 缓存入口
 *
 * @method HashDriver getDriver()
 *
 * @mixin HashDriver
 * @mixin HashStore
 * @mixin Repository
 */
class HashCache
{
    /**
     * 转发缓存调用
     */
    public function __call(string $method, array $arguments): mixed
    {
        return $this->store()->{$method}(...$arguments);
    }

    /**
     * 按输入顺序获取缓存值
     *
     * @param  list<string>  $keys
     */
    public function many(array $keys): array
    {
        $values = $this->store()->many($keys);

        return array_map(fn ($key) => $values[$key], $keys);
    }

    /**
     * 获取 Hash 缓存仓库
     */
    public function store(): Repository
    {
        return Cache::store('redis-hash');
    }
}
