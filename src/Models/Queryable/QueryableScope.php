<?php

declare(strict_types=1);

namespace Pin\Models\Queryable;

use Closure;
use Illuminate\Database\Eloquent\Builder;

/**
 * 查询条件作用域
 */
class QueryableScope
{
    /**
     * 应用查询条件
     */
    public static function query(
        Builder $builder,
        string $column,
        mixed $value,
        string|QueryableType $type = QueryableType::Eq
    ): Builder {
        if (blank($value)) {
            return $builder;
        }

        [$type, $columns] = QueryableType::parse($type);
        $column = implode('|', array_map(
            $builder->getModel()->transformQueryableColumn(...),
            $columns ?: explode('|', $column)
        ));

        return match (true) {
            $type->isRange() => static::applyRange($builder, $column, $value, $type),
            is_array($value), $type->isIn() => static::applyIn($builder, $column, $value, $type),
            $type->comparisonSymbol() !== null => static::applyCompare(
                $builder, $column, $value, $type
            ),
            $type->isLike() => static::applyLike($builder, $column, $value, $type),
            $type === QueryableType::Ns => static::applyNs($builder, $column, $value),
            default => $builder->where($column, static::value($value, $type, false)),
        };
    }

    /**
     * 创建查询宏
     */
    public static function queryable(): Closure
    {
        return function (Queryable|QueryableCondition|array|null $queryable) {
            /** @var Builder $this */
            if (! $queryable) {
                return $this;
            }

            if (is_array($queryable)) {
                $queryable = Queryable::fromRequest($queryable);
            } elseif ($queryable instanceof QueryableCondition) {
                return QueryableScope::query(
                    $this,
                    $queryable->column,
                    $queryable->value,
                    $queryable->type
                );
            }

            return QueryableScope::whereQueryable($this, $queryable);
        };
    }

    /**
     * 应用查询条件集合
     */
    public static function whereQueryable(Builder $builder, Queryable $queryable): Builder
    {
        foreach ($queryable->conditions as $condition) {
            static::query($builder, $condition->column, $condition->value, $condition->type);
        }

        return $queryable->apply($builder);
    }

    /**
     * 比较操作（>, >=, <, <=）
     */
    protected static function applyCompare(
        Builder $builder,
        string $column,
        mixed $value,
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
        mixed $value,
        QueryableType $type
    ): Builder {
        return $builder->whereIn($column, static::value($value, $type, true));
    }

    /**
     * LIKE 查询
     */
    protected static function applyLike(
        Builder $builder,
        string $column,
        mixed $value,
        QueryableType $type
    ): Builder {
        $value = static::likeValue((string) $value, $type);

        if (! str_contains($column, '|')) {
            return $builder->where($column, 'like', $value);
        }

        return $builder->where(static function (Builder $builder) use ($column, $value) {
            foreach (explode('|', $column) as $name) {
                $builder->orWhere($name, 'like', $value);
            }
        });
    }

    /**
     * 数字精确查询，文本模糊查询
     */
    protected static function applyNs(Builder $builder, string $column, mixed $value): Builder
    {
        $columns = explode('|', str_replace(',', '|', $column), 2);

        if (ctype_digit((string) $value)) {
            return $builder->where($columns[0], (int) $value);
        }

        return static::applyLike(
            $builder,
            $columns[1] ?? $columns[0],
            $value,
            QueryableType::Like
        );
    }

    /**
     * 区间查询
     */
    protected static function applyRange(
        Builder $builder,
        string $column,
        mixed $value,
        QueryableType $type
    ): Builder {
        $bounds = is_array($value) ? $value : explode(',', (string) $value);

        if (filled($bounds[0] ?? null)) {
            $builder->where($column, '>=', static::value($bounds[0], $type, false));
        }

        if (filled($bounds[1] ?? null)) {
            $builder->where($column, '<=', static::value($bounds[1], $type, false));
        }

        return $builder;
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
     * 转换查询值
     */
    protected static function value(
        mixed $value,
        QueryableType $type,
        bool $asArray
    ): mixed {
        if (! $asArray) {
            return $type->isNumeric() ? static::numericValue($value) : $value;
        }

        $value = is_array($value) ? $value : explode(',', (string) $value);

        return $type->isNumeric()
            ? array_map(static::numericValue(...), $value)
            : $value;
    }

    /**
     * 转换数值
     */
    protected static function numericValue(mixed $value): int|float
    {
        return is_numeric($value) ? $value + 0 : (float) $value;
    }
}
