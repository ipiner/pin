<?php

declare(strict_types=1);

namespace Pin\Models\Queryable;

use Pin\Validation\Rules\Queryable;

/**
 * Queryable
 *
 * Query DSL 规则解析器（非 Laravel Validation）
 *
 * 从 Validation rules() 中提取查询语义（Query Metadata）”，用于构建 Queryable。
 *
 * @internal
 */
class QueryableRuleExtractor
{
    /**
     * 从 Laravel rules() 中提取 Query DSL
     *
     * @param  array<string, mixed>  $rules  Validation rules()
     * @return array<string, string> field => query operator
     */
    public static function extract(array $rules): array
    {
        $conditions = [];

        foreach ($rules as $field => $items) {
            foreach (static::normalizeRules($items) as $rule) {
                if ($type = static::resolveRule($rule)) {
                    $conditions[$field] = $type;
                    break;
                }
            }
        }

        return $conditions;
    }

    /**
     * 解析单条 rule 中的 Query DSL 信息
     *
     * @param  mixed  $rule  Laravel validation rule
     */
    protected static function resolveRule(mixed $rule): ?string
    {
        // 对象规则
        if ($rule instanceof Queryable) {
            return $rule->type;
        }

        // q:like / q:eq / q:ns:id,name
        if (is_string($rule) && str_starts_with($rule, 'q:')) {
            return substr($rule, 2);
        }

        return null;
    }

    /**
     * 统一 rules 格式
     */
    protected static function normalizeRules(array|string $rules): array
    {
        return is_array($rules) ? $rules : explode('|', $rules);
    }
}
