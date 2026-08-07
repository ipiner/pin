<?php

declare(strict_types=1);

namespace Pin\Models\Scopes;

use Closure;
use Illuminate\Database\Eloquent\Builder;

/**
 * Sort Scope
 *
 * 提供 Eloquent 查询构建器的动态排序封装。
 * 支持传入允许的排序字段列表，并通过前缀 `-` 实现降序排序。
 */
class Sort
{
    /**
     * 动态排序方法封装
     */
    public static function sort(): Closure
    {
        return function (array|string|null $value, array|string $allows) {
            /** @var Builder $this */
            if (! $value) {
                return $this;
            }

            // 将允许字段和排序字段统一转换为数组
            $allows = is_array($allows) ? $allows : explode(',', $allows);
            $values = is_array($value) ? $value : explode(',', $value);

            foreach ($values as $item) {
                Sort::sortBy($this, $item, $allows);
            }

            return $this;
        };
    }

    /**
     * 根据单个字段排序
     */
    public static function sortBy(Builder $builder, string $value, array $allows): Builder
    {
        $column = $value;
        $direction = 'asc';

        // 处理降序字段前缀 "-"
        if (str_starts_with($value, '-')) {
            $column = substr($value, 1);
            $direction = 'desc';
        }

        // 仅允许排序字段生效
        return in_array($column, $allows) ? $builder->orderBy($column, $direction) : $builder;
    }
}
