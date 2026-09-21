<?php

declare(strict_types=1);

namespace Pin\Models\Queryable;

use Pin\Validation\Rules\Queryable;

/**
 * 查询规则提取器
 *
 * @internal
 */
class QueryableRuleExtractor
{
    /**
     * 从验证规则提取查询类型
     *
     * @param  array<string, array|string>  $rules
     * @return array<string, string>
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
     * 解析单条查询规则
     */
    protected static function resolveRule(mixed $rule): ?string
    {
        if ($rule instanceof Queryable) {
            return $rule->type;
        }

        if (is_string($rule) && str_starts_with($rule, 'q:')) {
            return substr($rule, 2);
        }

        return null;
    }

    /**
     * 标准化验证规则
     */
    protected static function normalizeRules(array|string $rules): array
    {
        return is_array($rules) ? $rules : explode('|', $rules);
    }
}
