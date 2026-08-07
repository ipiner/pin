<?php

declare(strict_types=1);

namespace Pin\Models\Cache;

/**
 * 缓存类型
 */
enum CacheType: int
{
    /**
     * 不持久化缓存
     */
    case None = 0;

    /**
     * 单条记录缓存
     *
     * 存储结构：
     * - {table}:{id} => model->toArray()
     */
    case CacheItem = 1;

    /**
     * 全量缓存（Hash 结构）
     *
     * 存储结构：
     * key: {table}-all
     * field: id
     * value: model->toArray()
     */
    case CacheAll = 2;
}
