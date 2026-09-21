<?php

declare(strict_types=1);

namespace Pin\Cache;

use Closure;
use Illuminate\Cache\Repository;

/**
 * 进程内缓存
 */
class RuntimeCache
{
    /**
     * 默认缓存时间（秒）
     */
    protected const int TTL = 86400;

    /**
     * 进程共享的缓存仓库
     */
    protected static Repository $repository;

    /**
     * 获取未过期的缓存
     *
     * @return array<array-key, mixed>
     */
    public function all(?string $prefix = null): array
    {
        return $this->repo()->getAll($prefix);
    }

    /**
     * 删除缓存
     */
    public function delete(string $key): bool
    {
        return $this->repo()->forget($key);
    }

    /**
     * 清空缓存
     */
    public function flush(): bool
    {
        return $this->repo()->clear();
    }

    /**
     * 获取缓存
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->repo()->get($key, $default);
    }

    /**
     * 获取或生成缓存
     */
    public function remember(string $key, Closure $callback, ?int $ttl = self::TTL): mixed
    {
        $value = $this->repo()->get($key);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->put($key, $value, $ttl);

        return $value;
    }

    /**
     * 获取或生成永久缓存
     */
    public function rememberForever(string $key, Closure $callback): mixed
    {
        return $this->remember($key, $callback, null);
    }

    /**
     * 获取缓存仓库
     */
    public function repo(): Repository
    {
        return static::$repository ??= new Repository(new ArrayStore(), ['store' => 'memoize']);
    }

    /**
     * 写入缓存
     */
    public function put(array|string $key, mixed $value = null, ?int $ttl = self::TTL): bool
    {
        return tap(
            is_array($key)
                ? $this->repo()->putMany($key, $value ?? $ttl)
                : $this->repo()->put($key, $value, $ttl),
            fn () => $this->repo()->getStore()->gc(),
        );
    }
}
