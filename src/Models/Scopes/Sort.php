<?php

declare(strict_types=1);

namespace Pin\Models\Scopes;

use Closure;
use Illuminate\Database\Eloquent\Builder;

/**
 * 排序查询宏
 */
class Sort
{
    /**
     * 创建排序宏
     */
    public static function sort(): Closure
    {
        return function (array|string|null $value, array|string $allows) {
            /** @var Builder $this */
            if (! $value) {
                return $this;
            }

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

        if (str_starts_with($value, '-')) {
            $column = substr($value, 1);
            $direction = 'desc';
        }

        return in_array($column, $allows, true) ? $builder->orderBy($column, $direction) : $builder;
    }
}
