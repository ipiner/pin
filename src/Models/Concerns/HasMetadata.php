<?php

declare(strict_types=1);

namespace Pin\Models\Concerns;

use Pin\Database\Schema\Metadata;

/**
 * 模型元数据
 */
trait HasMetadata
{
    /**
     * 获取模型元数据
     */
    public static function metadata(): Metadata
    {
        return Metadata::make(static::class);
    }
}
