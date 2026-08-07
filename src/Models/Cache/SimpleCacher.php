<?php

/** @noinspection PhpIncompatibleReturnTypeInspection */

declare(strict_types=1);

namespace Pin\Models\Cache;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Pin\Models\Model;

/**
 * 简化版模型缓存访问器
 *
 * 默认提供：
 * - L1:ArrayStore 当前请求
 * - L2:RedisHash 持久化
 *
 * CacheType::None
 * - L1 缓存
 * - 不使用缓存（直查 DB）
 *
 * CacheType::Item
 *  - 按 ID 单条缓存
 *  - 使用 remember(key) 模式
 *
 * CacheType::CacheAll
 *  - 全量缓存（一次加载全部）
 *  - 内存 keyBy('id') 后直接索引
 */
class SimpleCacher implements Cacher
{
    /**
     * 底层缓存存储（L1 + L2）
     */
    protected CacheStore $store;

    /**
     * 缓存过期时间（秒）
     *
     * 默认：7 天
     */
    protected int $ttl = 604800;

    /**
     * 模型实例（用于 hydrate / builder）
     */
    protected Model $m;

    /**
     * @param  class-string<Model>  $modelClass  模型类名
     * @param  string|null  $store  缓存驱动名称
     */
    public function __construct(protected string $modelClass, ?string $store = null)
    {
        $this->m = new $this->modelClass();

        $this->store = new CacheStore(
            $this->m,
            $this->m::cacheType() === CacheType::None
                ? null
                : Cache::store($store ?? 'redis-hash')
        );
    }

    /**
     * 删除缓存
     */
    public function forget(string $key): bool
    {
        return $this->store->forget($key);
    }

    /**
     * 获取单条模型
     */
    public function get(int $id): ?Model
    {
        // 全量缓存模式
        if ($this->m::cacheType() === CacheType::CacheAll) {
            return $this->getAll()[$id] ?? null;
        }

        // 单条缓存模式
        return $this->store->remember(
            KeyGenerator::forItem($this->m, $id),
            $this->ttl,
            fn () => $this->m::cacheBuilder()->find($id)
        );
    }

    /**
     * 获取全量模型集合
     *
     * - 自动 keyBy('id')
     */
    public function getAll(): Collection
    {
        return $this->store->rememberAll(
            KeyGenerator::forAll($this->m),
            $this->ttl,
            fn () => $this->m::cacheBuilder()->get(),
        );
    }

    /**
     * 设置缓存 TTL
     */
    public function ttl(int $seconds): static
    {
        $this->ttl = $seconds;

        return $this;
    }
}
