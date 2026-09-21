<?php

declare(strict_types=1);

namespace Pin\Tree\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Override;
use Pin\Models\Cache\CacheType;
use Pin\Support\Facades\Tree;

/**
 * 树节点查询
 */
trait TreeQuery
{
    /**
     * 构建缓存查询
     *
     * @return Builder<static>
     */
    #[Override]
    public static function cacheBuilder(): Builder
    {
        return static::orderedQuery();
    }

    /**
     * 获取全部节点
     *
     * @return Collection<int, static>
     */
    #[Override]
    public static function findAll(): Collection
    {
        $items = parent::findAll();

        return static::cacheType() === CacheType::CacheAll ? Tree::sort($items) : $items;
    }

    /**
     * 按层级、排序值和 ID 查询
     *
     * @return Builder<static>
     */
    public static function orderedQuery(): Builder
    {
        return static::query()
            ->orderBy('level')
            ->orderBy('sort')
            ->orderBy('id');
    }
}
