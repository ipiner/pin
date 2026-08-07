<?php

declare(strict_types=1);

namespace Pin\Models\Concerns;

use Pin\Models\Cache\CacheType;

/**
 * 标记模型使用单条记录缓存
 */
trait CacheItem
{
    /**
     * 单条缓存
     */
    public static function cacheType(): CacheType
    {
        return CacheType::CacheItem;
    }
}
