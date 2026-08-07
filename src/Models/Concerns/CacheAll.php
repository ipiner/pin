<?php

declare(strict_types=1);

namespace Pin\Models\Concerns;

use Pin\Models\Cache\CacheType;

/**
 * 标记模型使用全量数据缓存
 */
trait CacheAll
{
    /**
     * 全量缓存
     */
    public static function cacheType(): CacheType
    {
        return CacheType::CacheAll;
    }
}
