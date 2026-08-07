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
 * 模型缓存存储层（双层缓存架构：L1 + L2）
 *
 * L1（本地缓存）：
 * - 基于 array store（进程内）
 * - 生命周期短（当前请求或短期复用）
 * - 用于减少重复反序列化 / Redis IO
 *
 * L2（分布式缓存）：
 * - 如 Redis
 * - 跨请求共享数据
 */
class CacheStore
{
    public function __construct(protected Model $m, protected ?Repository $repo)
    {
    }

    /**
     * 删除缓存（同时清理 L1 + L2）
     *
     * @param  string  $key  模型缓存 key
     */
    public function forget(string $key): bool
    {
        // 清理 单条缓存
        $this->l1store()->forget($this->l1key($key));
        $this->repo?->forget($key);

        // 清理 全量缓存
        if ($this->m::cacheType() === CacheType::CacheAll) {
            $keyAll = KeyGenerator::forAll($this->m);
            $this->l1store()->forget($this->l1key($keyAll));

            // 需要删除整个缓存， del 替代 forget
            $this->repo?->del($keyAll);
        }

        return true;
    }

    /**
     * 单条缓存读取（带 fallback + L1 回填）
     */
    public function remember(string $key, int $ttl, Closure $callback): ?Model
    {
        $cached = $this->get($key);

        if ($cached instanceof Model) {
            return $cached;
        }

        if ($cached instanceof NullPlaceholder) {
            return null;
        }

        return tap(
            $callback(),
            fn ($value) => $this->put($key, $value, $ttl)
        );
    }

    /**
     * 全量缓存读取（Collection）
     *
     * 保证返回结构必须为 keyBy('id')
     */
    public function rememberAll(string $key, int $ttl, Closure $callback): Collection
    {
        $cached = $this->getAll($key);
        if ($cached !== null) {
            return $cached;
        }

        $value = $callback();
        if ($value->isEmpty()) {
            return $value;
        }

        // 强制结构统一（避免调用方结构不一致）
        $value = $value->keyBy('id');

        return tap(
            $value,
            fn () => $this->putAll($key, $value, $ttl)
        );
    }

    /**
     * 获取存储 `Repository`
     */
    public function repo(): ?Repository
    {
        return $this->repo;
    }

    /**
     * 获取单条缓存（L1 → L2）
     *
     * 返回类型：
     * - Model
     * - NullPlaceholder（空值缓存）
     * - null（未命中）
     */
    protected function get(string $key): Model|NullPlaceholder|null
    {
        // L1
        if (($value = $this->getFromStore($this->l1store(), $this->l1key($key))) !== null) {
            return $value;
        }

        // L2 + L1 回填
        $value = $this->repo ? $this->getFromStore($this->repo, $key) : null;
        if ($value !== null) {
            $this->l1store()->put($this->l1key($key), $value);
        }

        return $value;
    }

    /**
     * 获取全量缓存（L1 → L2）
     */
    protected function getAll(string $key): ?Collection
    {
        // L1
        $data = $this->l1store()->get($this->l1key($key));
        if ($data !== null) {
            return $data;
        }

        // L2 + L1 回填
        $data = $this->repo?->getAll($key);
        if ($data) {
            $data = $this->hydrateCollection($data)->keyBy('id');
            $this->l1store()->put($this->l1key($key), $data);
        }

        return $data ?: null;
    }

    /**
     * 内部解析缓存值（Model / Placeholder）
     */
    protected function getFromStore(Repository $repo, string $key): Model|NullPlaceholder|null
    {
        $value = $repo->get($key);

        if ($value === null) {
            return null;
        }

        // L1 有可能返回 NullPlaceholder
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

        return $this->m->newFromBuilder($value);
    }

    /**
     * Collection 反序列化
     */
    protected function hydrateCollection(array $data): Collection
    {
        return $this->m->query()->hydrate($data);
    }

    /**
     * L1 Key 生成（避免 L2 冲突）
     */
    protected function l1key(string $key): string
    {
        return static::class.'.'.$key;
    }

    /**
     * L1 store
     */
    protected function l1store(): Repository
    {
        return Cache::store('array');
    }

    /**
     * 写入单条模型缓存
     *
     * @param  string  $key  缓存 key
     * @param  Model|null  $value  模型数据（null 表示空值缓存）
     * @param  int|null  $ttl  L2 过期时间
     *
     * 行为说明：
     * - value != null：正常缓存
     * - value == null：写入 NullPlaceholder（防缓存穿透）
     */
    protected function put(string $key, ?Model $value, int $ttl): void
    {
        // 正常数据缓存
        if ($value !== null) {
            $this->l1store()->put($this->l1key($key), $value, $ttl);
            $this->repo?->put($key, $this->serialize($value), $ttl);

            return;
        }

        // 空值缓存（防缓存穿透）
        $l1Expires = max($ttl, 3600);
        $holder = NullPlaceholder::make($l1Expires);

        $this->l1store()->put($this->l1key($key), $holder, $l1Expires);
        $this->repo?->put($key, $holder->toString(), $ttl);
    }

    /**
     * 写入全量缓存（Collection）
     *
     * 仅适用于 CacheAll 模式模型
     */
    protected function putAll(string $key, Collection $value, int $ttl): void
    {
        // L1 缓存（进程内）
        $this->l1store()->put($this->l1key($key), $value, $ttl);

        // 不满足条件则不写 L2
        if (
            ! $this->repo ||
            $value->isEmpty() ||
            $this->m::cacheType() !== CacheType::CacheAll
        ) {
            return;
        }

        // L2 拆分缓存
        $items = [];

        foreach ($value as $item) {
            $items[KeyGenerator::forItem($key, $item->id)] = $this->serialize($item);
        }

        $this->repo->putMany($items, $ttl);
    }

    /**
     * 模型序列化（仅 attributes）
     */
    protected function serialize(Model $m): array
    {
        return $m->getAttributes();
    }
}
