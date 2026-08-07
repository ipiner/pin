<?php

declare(strict_types=1);

namespace Pin\Models\Queryable;

use Closure;
use Illuminate\Database\Eloquent\Builder;

/**
 * 查询作用域引擎（Query Scope Engine）
 *
 * - 将自定义查询 DSL 转换为 Eloquent 查询条件
 * - 动态查询、复杂筛选、前端参数解析
 */
class QueryableScope
{
    /**
     * 查询分发核心
     */
    public static function query(
        Builder $builder,
        string $column,
        string|array|null $value,
        string|QueryableType $type = QueryableType::Eq
    ): Builder {
        if (blank($value)) {
            return $builder;
        }

        [$type, $params] = QueryableType::parse($type);

        // like:title|content
        if ($params) {
            $column = implode('|', $params);
        }

        return match (true) {
            // in
            is_array($value), $type->isIn() => static::applyIn($builder, $column, $value, $type),

            // > / >= / < / <=
            $type->comparisonSymbol() !== null => static::applyCompare($builder, $column, $value, $type),

            // like
            $type->isLike() => static::applyLike($builder, $column, $value, $type),

            // 区间查询
            $type->isRange() => static::applyRange($builder, $column, $value, $type),

            // q:id|name
            $type === QueryableType::Ns => static::applyNs($builder, $column, $value),

            default => $builder->where($column, static::value($value, $type, false)),
        };
    }

    /**
     * 注册为 Builder 宏入口
     */
    public static function queryable(): Closure
    {
        return function (Queryable|QueryableCondition|array|null $queryable) {
            if ($queryable === null) {
                return $this;
            }

            if (is_array($queryable)) {
                $queryable = Queryable::fromRequest($queryable);
            } elseif ($queryable instanceof QueryableCondition) {
                $condition = $queryable;
                $queryable = new Queryable([], []);
                $queryable->conditions[$condition->column] = $condition;
            }

            QueryableScope::whereQueryable($this, $queryable);

            return $this;
        };
    }

    /**
     * 处理 Queryable 对象
     */
    public static function whereQueryable(Builder $builder, Queryable $queryable): Builder
    {
        foreach ($queryable->conditions as $condition) {
            $column = $builder->getModel()->transformQueryableColumn($condition->column);
            static::query($builder, $column, $condition->value, $condition->type);
        }

        return $queryable->apply($builder);
    }

    /**
     * 比较操作（>, >=, <, <=）
     */
    protected static function applyCompare(
        Builder $builder,
        string $column,
        string $value,
        QueryableType $type
    ): Builder {
        return $builder->where(
            $column,
            $type->comparisonSymbol(),
            static::value($value, $type, false)
        );
    }

    /**
     * IN 查询
     */
    protected static function applyIn(
        Builder $builder,
        string $column,
        string|array $value,
        QueryableType $type
    ): Builder {
        return $builder->whereIn($column, static::value($value, $type, true));
    }

    /**
     * LIKE 查询（支持多字段）
     *
     * column: title|description
     */
    protected static function applyLike(
        Builder $builder,
        string $column,
        mixed $value,
        QueryableType $type
    ): Builder {
        $value = static::likeValue($value, $type);

        if (! str_contains($column, '|')) {
            return $builder->where($column, 'like', $value);
        }

        $builder->where(function (Builder $builder) use ($column, $value) {
            foreach (explode('|', $column) as $name) {
                $builder->orWhere($name, 'like', $value);
            }
        });

        return $builder;
    }

    /**
     * NS 查询（智能匹配）
     *
     * 示例：
     * ns:id|name
     * ns:id,name
     *
     * - 数字 → where id = value
     * - 字符串 → where name like %value%
     */
    protected static function applyNs(Builder $builder, string $column, mixed $value): Builder
    {
        $arr = explode(
            '|',
            str_replace(',', '|', $column),
            2
        );

        if (ctype_digit($value)) {
            $builder->where($arr[0], (int) $value);
        } else {
            static::applyLike($builder, $arr[1], $value, QueryableType::Like);
        }

        return $builder;
    }

    /**
     * 区间查询
     *
     * value: "start,end"
     */
    protected static function applyRange(
        Builder $builder,
        string $column,
        mixed $value,
        QueryableType $type
    ): Builder {
        $arr = explode(',', $value);

        return $builder
            ->when(
                filled($arr[0] ?? null),
                fn () => $builder->where($column, '>=', static::value($arr[0], $type, false)))
            ->when(
                filled($arr[1] ?? null),
                fn () => $builder->where($column, '<=', static::value($arr[1], $type, false)));
    }

    /**
     * 生成 LIKE 查询值
     */
    protected static function likeValue(string $value, QueryableType $type): string
    {
        return match ($type) {
            QueryableType::StartsWith => $value.'%',
            QueryableType::EndsWith => '%'.$value,
            default => '%'.$value.'%',
        };
    }

    /**
     * 统一值处理
     */
    protected static function value(
        array|string $value,
        QueryableType $type,
        bool $asArray
    ): array|float|string {
        if (! $asArray) {
            return $type->isNumeric() ? (float) $value : $value;
        }

        $value = is_array($value) ? $value : explode(',', $value);

        return $type->isNumeric()
            ? array_map('floatval', $value)
            : $value;
    }
}
