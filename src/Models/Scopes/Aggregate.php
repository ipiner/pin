<?php

declare(strict_types=1);

namespace Pin\Models\Scopes;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Aggregate Scopes
 *
 * 提供 Eloquent 查询构建器的聚合函数封装，方便在 group by 查询中使用
 */
class Aggregate
{
    /**
     * 添加 avg 聚合字段
     */
    public function addSelectAvg(): Closure
    {
        return function (string $column, ?string $alias = null) {
            /** @var Builder $this */
            return $this->addSelect(
                DB::raw("avg($column) as ".($alias ?: "avg_{$column}"))
            );
        };
    }

    /**
     * 添加 count 聚合字段
     */
    public function addSelectCount(): Closure
    {
        return function (string $column = '*', string $alias = 'total') {
            /** @var Builder $this */
            return $this->addSelect(
                DB::raw("count($column) as ".($alias ?: "count_{$column}"))
            );
        };
    }

    /**
     * 添加 max 聚合字段
     */
    public function addSelectMax(): Closure
    {
        return function (string $column, ?string $alias = null) {
            /** @var Builder $this */
            return $this->addSelect(
                DB::raw("max($column) as ".($alias ?: "max_{$column}"))
            );
        };
    }

    /**
     * 添加 min 聚合字段
     */
    public function addSelectMin(): Closure
    {
        return function (string $column, ?string $alias = null) {
            /** @var Builder $this */
            return $this->addSelect(
                DB::raw("min($column) as ".($alias ?: "min_{$column}"))
            );
        };
    }

    /**
     * 添加 sum 聚合字段
     */
    public function addSelectSum(): Closure
    {
        return function (string $column, ?string $alias = null) {
            /** @var Builder $this */
            return $this->addSelect(
                DB::raw("sum($column) as ".($alias ?: "sum_{$column}"))
            );
        };
    }
}
