<?php

declare(strict_types=1);

namespace Pin\Cache;

use Closure;
use Illuminate\Cache\Repository;

/**
 * 进程内缓存
 *
 * 数据仅在当前 PHP 进程生命周期内有效。
 */
class RuntimeCache
{
    /**
     * 缓存 TTL（秒）
     *
     * 默认 1 天
     *
     * @var int
     */
    protected const int TTL = 86400;

    /**
     * 内部缓存存储仓库 `Repository`
     *
     * 进程级共享（static）
     */
    protected static Repository $repo;

    /**
     * 获取当前缓存中的所有数据（支持按前缀过滤）
     *
     * @param  string|null  $prefix  key 前缀（如 "menus:"）
     * @return array<string, mixed> 返回 key => value 结构
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
     * 获取缓存（带懒加载）
     *
     * 类似 Laravel -> Cache::remember()
     */
    public function remember(string $key, Closure $callback, ?int $ttl = self::TTL): mixed
    {
        $value = $this->repo()->get($key);

        // 命中
        if ($value !== null) {
            return $value;
        }

        // 未命中 → 执行回调
        return tap($callback(), fn ($value) => $this->put($key, $value, $ttl));
    }

    /**
     * 永久缓存
     */
    public function rememberForever(string $key, Closure $callback): mixed
    {
        return $this->remember($key, $callback, null);
    }

    /**
     * 获取存储仓库 `Repository`
     */
    public function repo(): Repository
    {
        return static::$repo ??= new Repository(new ArrayStore(), ['store' => 'memoize']);
    }

    /**
     * 设置缓存
     */
    public function put(array|string $key, mixed $value = null, ?int $ttl = self::TTL): bool
    {
        return tap(
            $this->repo()->put($key, $value, $ttl),
            fn () => $this->repo()->getStore()->gc());
    }
}
