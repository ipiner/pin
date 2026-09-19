<?php

declare(strict_types=1);

namespace Pin\Models\Cache;

use Closure;
use Illuminate\Cache\Repository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Pin\Models\Model;
use Pin\Support\Json;

/**
 * 模型本地缓存与持久缓存
 */
class CacheStore
{
    public function __construct(protected Model $model, protected ?Repository $repository)
    {
    }

    /**
     * 删除模型缓存
     */
    public function forget(string $key): bool
    {
        $this->l1store()->forget($this->l1key($key));
        $this->repository?->forget($key);

        $allKey = KeyGenerator::forAll($this->model);
        $this->l1store()->forget($this->l1key($allKey));

        if ($this->model::cacheType() === CacheType::CacheAll) {
            $this->repository?->del($allKey);
        }

        return true;
    }

    /**
     * 读取或填充单条缓存
     */
    public function remember(string $key, int $ttl, Closure $callback): ?Model
    {
        $cached = $this->get($key, $ttl);

        if ($cached instanceof Model) {
            return $cached;
        }

        if ($cached instanceof NullPlaceholder) {
            return null;
        }

        $value = $callback();
        $this->put($key, $value, $ttl);

        return $value;
    }

    /**
     * 读取或填充全量缓存。
     *
     * @return Collection<int, Model>
     */
    public function rememberAll(string $key, int $ttl, Closure $callback): Collection
    {
        $cached = $this->getAll($key, $ttl);

        if ($cached) {
            return $cached;
        }

        $value = $callback()->keyBy('id');
        $this->putAll($key, $value, $ttl);

        return $value;
    }

    /**
     * 获取持久缓存仓库
     */
    public function repo(): ?Repository
    {
        return $this->repository;
    }

    /**
     * 获取单条缓存
     */
    protected function get(string $key, int $ttl): Model|NullPlaceholder|null
    {
        $store = $this->l1store();
        $localKey = $this->l1key($key);

        if ($value = $this->getFromStore($store, $localKey)) {
            return $value;
        }

        $value = $this->repository ? $this->getFromStore($this->repository, $key) : null;

        if ($value) {
            $store->put($localKey, $value, $ttl);
        }

        return $value;
    }

    /**
     * 获取全量缓存
     */
    protected function getAll(string $key, int $ttl): ?Collection
    {
        $store = $this->l1store();
        $localKey = $this->l1key($key);

        if ($data = $store->get($localKey)) {
            return $data;
        }

        $data = $this->repository?->getAll($key);

        if (! $data) {
            return null;
        }

        $data = $this->hydrateCollection($data)->keyBy('id');
        $store->put($localKey, $data, $ttl);

        return $data;
    }

    /**
     * 读取并还原缓存值
     */
    protected function getFromStore(Repository $repo, string $key): Model|NullPlaceholder|null
    {
        $value = $repo->get($key);

        if ($value === null) {
            return null;
        }

        $holder = $value instanceof NullPlaceholder ? $value : NullPlaceholder::parse($value);

        if ($holder) {
            return $holder->isExpired() ? null : $holder;
        }

        return $this->hydrate($value);
    }

    /**
     * 单条数据反序列化
     */
    protected function hydrate(Model|array|string $value): Model
    {
        if ($value instanceof Model) {
            return $value;
        }

        if (is_string($value)) {
            $value = Json::decode($value);
        }

        return $this->model->newFromBuilder($value);
    }

    /**
     * 还原模型集合
     */
    protected function hydrateCollection(array $data): Collection
    {
        return $this->model->newQuery()->hydrate($data);
    }

    /**
     * 生成本地缓存键
     */
    protected function l1key(string $key): string
    {
        return static::class.'.'.$key;
    }

    /**
     * 获取本地缓存仓库
     */
    protected function l1store(): Repository
    {
        return Cache::store('array');
    }

    /**
     * 写入单条缓存
     */
    protected function put(string $key, ?Model $value, int $ttl): void
    {
        if ($value) {
            $this->l1store()->put($this->l1key($key), $value, $ttl);
            $this->repository?->put($key, $this->serialize($value), $ttl);

            return;
        }

        $holder = NullPlaceholder::make($ttl);

        $this->l1store()->put($this->l1key($key), $holder, $ttl);
        $this->repository?->put($key, $holder->toString(), $ttl);
    }

    /**
     * 写入全量缓存
     */
    protected function putAll(string $key, Collection $value, int $ttl): void
    {
        $this->l1store()->put($this->l1key($key), $value, $ttl);

        if (
            ! $this->repository
            || $value->isEmpty()
            || $this->model::cacheType() !== CacheType::CacheAll
        ) {
            return;
        }

        $items = [];

        foreach ($value as $item) {
            $items[KeyGenerator::forItem($key, $item->id)] = $this->serialize($item);
        }

        $this->repository->putMany($items, $ttl);
    }

    /**
     * 序列化模型属性
     */
    protected function serialize(Model $model): array
    {
        return $model->getAttributes();
    }
}
