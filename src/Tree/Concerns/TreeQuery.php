<?php

declare(strict_types=1);

namespace Pin\Tree\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Pin\Models\Cache\CacheType;
use Pin\Support\Facades\Tree;

/**
 * TreeQuery
 *
 * 树形结构的数据查询层，负责提供所有与数据库相关的 Tree 查询能力（排序、子树查询等）。
 */
trait TreeQuery
{
    /**
     * Tree 缓存查询入口（统一排序查询）
     */
    public static function cacheBuilder(): Builder
    {
        return static::orderedQuery();
    }

    /**
     * 获取所有数据
     *
     * 如果是整表缓存，从 Redis Hash 返回数所后，对结果集排序
     */
    public static function findAll(): Collection
    {
        $items = parent::findAll();

        if (static::cacheType() === CacheType::CacheAll) {
            $items = Tree::sort($items);
        }

        return $items;
    }

    /**
     * 构建标准树排序查询
     */
    public static function orderedQuery(): Builder
    {
        return static::query()
            ->orderBy('level')
            ->orderBy('sort')
            ->orderBy('id');
    }
}
