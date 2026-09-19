<?php

declare(strict_types=1);

namespace Pin\Validation;

use Pin\Models\Queryable\QueryableType;
use Pin\Validation\Rules\Queryable;

/**
 * 查询验证规则工厂。
 */
class QueryableRules
{
    /**
     * 后缀匹配查询。
     */
    public static function endsWith(mixed ...$rules): array
    {
        return static::string(QueryableType::EndsWith, ...$rules);
    }

    /**
     * 字符串等值查询。
     */
    public static function eq(mixed ...$rules): array
    {
        return static::string(QueryableType::Eq, ...$rules);
    }

    /**
     * 数值等值查询。
     */
    public static function eqNumeric(mixed ...$rules): array
    {
        return static::number(QueryableType::EqNumeric, ...$rules);
    }

    /**
     * 字符串大于查询。
     */
    public static function gt(mixed ...$rules): array
    {
        return static::string(QueryableType::Gt, ...$rules);
    }

    /**
     * 数值大于查询。
     */
    public static function gtNumeric(mixed ...$rules): array
    {
        return static::number(QueryableType::GtNumeric, ...$rules);
    }

    /**
     * 字符串大于等于查询。
     */
    public static function gte(mixed ...$rules): array
    {
        return static::string(QueryableType::Gte, ...$rules);
    }

    /**
     * 数值大于等于查询。
     */
    public static function gteNumeric(mixed ...$rules): array
    {
        return static::number(QueryableType::GteNumeric, ...$rules);
    }

    /**
     * 字符串 IN 查询。
     *
     * @param  string  $type  值类型规则
     */
    public static function in(string $type = 'array', mixed ...$rules): array
    {
        return static::rule(QueryableType::In, $type, ...$rules);
    }

    /**
     * 数值 IN 查询。
     *
     * @param  string  $type  值类型规则
     */
    public static function inNumeric(string $type = 'array', mixed ...$rules): array
    {
        return static::rule(QueryableType::InNumeric, $type, ...$rules);
    }

    /**
     * 模糊查询。
     */
    public static function like(mixed ...$rules): array
    {
        return static::string(QueryableType::Like, ...$rules);
    }

    /**
     * 字符串小于查询。
     */
    public static function lt(mixed ...$rules): array
    {
        return static::string(QueryableType::Lt, ...$rules);
    }

    /**
     * 数值小于查询。
     */
    public static function ltNumeric(mixed ...$rules): array
    {
        return static::number(QueryableType::LtNumeric, ...$rules);
    }

    /**
     * 字符串小于等于查询。
     */
    public static function lte(mixed ...$rules): array
    {
        return static::string(QueryableType::Lte, ...$rules);
    }

    /**
     * 数值小于等于查询。
     */
    public static function lteNumeric(mixed ...$rules): array
    {
        return static::number(QueryableType::LteNumeric, ...$rules);
    }

    /**
     * 智能搜索查询。
     *
     * @param  string  $fields  查询字段，逗号或竖线分隔
     */
    public static function ns(string $fields, mixed ...$rules): array
    {
        return static::string(QueryableType::Ns->asRule($fields), ...$rules);
    }

    /**
     * 字符串区间查询。
     */
    public static function range(mixed ...$rules): array
    {
        return static::string(QueryableType::Range, ...$rules);
    }

    /**
     * 数值区间查询。
     */
    public static function rangeNumeric(mixed ...$rules): array
    {
        return static::string(QueryableType::RangeNumeric, ...$rules);
    }

    /**
     * 前缀匹配查询。
     */
    public static function startsWith(mixed ...$rules): array
    {
        return static::string(QueryableType::StartsWith, ...$rules);
    }

    /**
     * 数值字段查询。
     */
    protected static function number(
        Queryable|QueryableType|null $queryableRule = null,
        mixed ...$rules
    ): array {
        return static::rule($queryableRule ?? QueryableType::EqNumeric, 'numeric', ...$rules);
    }

    /**
     * 组合查询与验证规则。
     *
     * @return array<array-key, mixed>
     */
    protected static function rule(Queryable|QueryableType $queryableRule, mixed ...$rules): array
    {
        return [
            'nullable',
            ...$rules,
            $queryableRule instanceof QueryableType
                ? $queryableRule->asRule()
                : $queryableRule,
        ];
    }

    /**
     * 字符串字段查询。
     */
    protected static function string(
        Queryable|QueryableType|null $queryableRule = null,
        mixed ...$rules
    ): array {
        return static::rule($queryableRule ?? QueryableType::Like, 'string', ...$rules);
    }
}
