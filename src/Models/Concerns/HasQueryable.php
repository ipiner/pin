<?php

declare(strict_types=1);

namespace Pin\Models\Concerns;

use Illuminate\Support\Str;

/**
 * 查询字段转换
 */
trait HasQueryable
{
    /**
     * 转换查询字段名
     */
    public function transformQueryableColumn(string $column): string
    {
        return Str::snake($column);
    }
}
