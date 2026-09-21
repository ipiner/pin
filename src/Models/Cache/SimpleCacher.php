<?php

declare(strict_types=1);

namespace Pin\Models\Cache;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Override;
use Pin\Models\Model;

/**
 * 模型缓存访问器
 */
class SimpleCacher implements Cacher
{
    /**
     * 缓存存储
     */
    protected CacheStore $store;

    /**
     * 缓存过期时间（秒）
     */
    protected int $ttl = 604800;

    /**
     * 模型实例
     */
    protected Model $model;

    /**
     * @param  class-string<Model>  $modelClass  模型类名
     * @param  string|null  $store  缓存驱动名称
     */
    public function __construct(protected string $modelClass, ?string $store = null)
    {
        $this->model = new $this->modelClass();

        $this->store = new CacheStore(
            $this->model,
            $this->model::cacheType() === CacheType::None
                ? null
                : Cache::store($store ?? 'redis-hash')
        );
    }

    /**
     * 删除缓存
     */
    #[Override]
    public function forget(string $key): bool
    {
        return $this->store->forget($key);
    }

    /**
     * 获取单条模型
     */
    #[Override]
    public function get(int $id): ?Model
    {
        if ($this->model::cacheType() === CacheType::CacheAll) {
            return $this->getAll()[$id] ?? null;
        }

        return $this->store->remember(
            KeyGenerator::forItem($this->model, $id),
            $this->ttl,
            fn () => $this->model::cacheBuilder()->find($id)
        );
    }

    /**
     * 获取全量模型集合
     *
     * @return Collection<int, Model>
     */
    #[Override]
    public function getAll(): Collection
    {
        return $this->store->rememberAll(
            KeyGenerator::forAll($this->model),
            $this->ttl,
            fn () => $this->model::cacheBuilder()->get(),
        );
    }

    /**
     * 设置缓存有效期（秒）
     */
    #[Override]
    public function ttl(int $seconds): static
    {
        $this->ttl = $seconds;

        return $this;
    }
}
