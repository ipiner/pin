<?php

declare(strict_types=1);

namespace Pin\Models\Queryable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * 查询条件集合
 */
class Queryable
{
    /**
     * @var array<string, QueryableCondition>
     */
    public array $conditions = [];

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, string|QueryableType>  $types
     */
    public function __construct(public array $payload, array $types)
    {
        foreach ($types as $column => $type) {
            $this->conditions[$column] = new QueryableCondition(
                $column,
                $payload[$column] ?? null,
                $type
            );
        }
    }

    /**
     * 从查询参数创建实例
     *
     * @param  array<string, mixed>  $payload
     * @param  array<string, string|QueryableType>  $types
     */
    public static function fromPayload(array $payload, array $types): static
    {
        return new static($payload, $types);
    }

    /**
     * 从请求参数创建实例
     *
     * @param  array<string, string|QueryableType>  $types
     */
    public static function fromRequest(array $types, ?Request $request = null): static
    {
        return new static(static::resolvePayload($request), $types);
    }

    /**
     * 从验证规则创建实例
     *
     * @param  array<string, mixed>  $rules
     */
    public static function fromRules(array $rules, Request|array|null $payload = null): static
    {
        return new static(
            static::resolvePayload($payload),
            QueryableRuleExtractor::extract($rules)
        );
    }

    /**
     * 应用附加查询条件
     */
    public function apply(Builder $builder): Builder
    {
        return $builder;
    }

    /**
     * 解析查询参数
     *
     * @return array<string, mixed>
     */
    protected static function resolvePayload(Request|array|null $payload): array
    {
        return match (true) {
            is_array($payload) => $payload,
            $payload instanceof Request => $payload->query(),
            default => request()->query(),
        };
    }
}
