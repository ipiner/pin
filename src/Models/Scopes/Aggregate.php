<?php

declare(strict_types=1);

namespace Pin\Models\Scopes;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;

/**
 * 聚合查询宏
 */
class Aggregate
{
    /**
     * 添加 avg 聚合字段
     */
    public function addSelectAvg(): Closure
    {
        return $this->aggregate('avg');
    }

    /**
     * 添加 count 聚合字段
     */
    public function addSelectCount(): Closure
    {
        return function (string $column = '*', string $alias = 'total') {
            /** @var Builder $this */
            return $this->addSelect(
                new Expression("count($column) as ".($alias ?: "count_{$column}"))
            );
        };
    }

    /**
     * 添加 max 聚合字段
     */
    public function addSelectMax(): Closure
    {
        return $this->aggregate('max');
    }

    /**
     * 添加 min 聚合字段
     */
    public function addSelectMin(): Closure
    {
        return $this->aggregate('min');
    }

    /**
     * 添加 sum 聚合字段
     */
    public function addSelectSum(): Closure
    {
        return $this->aggregate('sum');
    }

    /**
     * 创建聚合字段宏
     */
    private function aggregate(string $function): Closure
    {
        return function (string $column, ?string $alias = null) use ($function) {
            /** @var Builder $this */
            return $this->addSelect(
                new Expression("$function($column) as ".($alias ?: "{$function}_{$column}"))
            );
        };
    }
}
