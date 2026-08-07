<?php

declare(strict_types=1);

namespace Pin\Models\Concerns;

use Pin\Database\Schema\Metadata;

/**
 * 模型元数据
 *
 * 为 Model 提供统一的元数据访问能力。
 *
 * 元数据来源：`database/schemas/{connection}/{table}.php`
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
