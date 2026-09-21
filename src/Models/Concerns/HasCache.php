<?php

declare(strict_types=1);

namespace Pin\Models\Concerns;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Pin\Models\Cache\Cacher;
use Pin\Models\Cache\CacheType;
use Pin\Models\Cache\KeyGenerator;
use Pin\Models\Cache\SimpleCacher;
use Pin\Models\Model;

/**
 * 模型缓存能力
 */
trait HasCache
{
    /**
     * 模型缓存实例池
     *
     * @var array<class-string<Model>, Cacher>
     */
    protected static array $cacher = [];

    /**
     * 注册缓存失效事件
     */
    public static function bootHasCache(): void
    {
        static::saved(static fn (Model $model) => $model->forgetCache());
        static::deleted(static fn (Model $model) => $model->forgetCache());
    }

    /**
     * 缓存查询构造器
     */
    public static function cacheBuilder(): Builder
    {
        return static::query();
    }

    /**
     * 获取缓存策略
     */
    public static function cacheType(): CacheType
    {
        return CacheType::None;
    }

    /**
     * 获取单条模型
     */
    public static function find(int $id): ?static
    {
        if ($id < 1) {
            return null;
        }

        return static::cacher()->get($id);
    }

    /**
     * 获取全量数据
     *
     * @return Collection<int, static>
     */
    public static function findAll(): Collection
    {
        return static::cacher()->getAll();
    }

    /**
     * 按字段查找单条模型
     *
     * @param  string  $column  查询字段
     * @param  string|int  $value  查询值
     */
    public static function findBy(string $column, string|int $value): ?static
    {
        if (static::cacheType() === CacheType::CacheAll) {
            return static::findAll()->first(
                static fn ($item) => strcasecmp((string) $item[$column], (string) $value) === 0
            );
        }

        return static::where($column, $value)->first();
    }

    /**
     * 批量获取模型
     *
     * @return Collection<int, static> keyBy('id') 结构
     */
    public static function findMany(array|Arrayable $ids): Collection
    {
        $ids = $ids instanceof Arrayable ? $ids->toArray() : $ids;

        if (static::cacheType() === CacheType::CacheAll) {
            return static::findAll()->whereIn('id', $ids);
        }

        return static::whereIn('id', $ids)->get()->keyBy('id');
    }

    /**
     * 获取单条模型，不存在时抛出异常
     *
     * @throws ModelNotFoundException
     */
    public static function findOrFail(int $id): static
    {
        if ($item = static::find($id)) {
            return $item;
        }

        throw new ModelNotFoundException()->setModel(static::class, $id);
    }

    /**
     * 获取缓存实例
     */
    protected static function cacher(): Cacher
    {
        return static::$cacher[static::class] ??= new SimpleCacher(static::class);
    }

    /**
     * 删除当前模型缓存
     */
    protected function forgetCache(): void
    {
        static::cacher()->forget(
            KeyGenerator::forItem($this->getTable(), $this->id)
        );
    }
}
