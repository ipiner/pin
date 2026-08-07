<?php

declare(strict_types=1);

namespace Pin\Validation;

use Pin\Models\Queryable\QueryableType;
use Pin\Validation\Rules\Queryable;

/**
 * 为 Laravel 验证规则声明可查询字段
 */
class QueryableRules
{
    /**
     * 后缀匹配查询。
     *
     * @param  mixed  ...$rules  附加到字段上的 Laravel 验证规则
     */
    public static function endsWith(...$rules): array
    {
        return static::string(QueryableType::EndsWith, ...$rules);
    }

    /**
     * 字符串等值查询。
     *
     * @param  mixed  ...$rules  附加到字段上的 Laravel 验证规则
     */
    public static function eq(...$rules): array
    {
        return static::string(QueryableType::Eq, ...$rules);
    }

    /**
     * 数值等值查询。
     *
     * @param  mixed  ...$rules  附加到字段上的 Laravel 验证规则
     */
    public static function eqNumeric(...$rules): array
    {
        return static::number(QueryableType::EqNumeric, ...$rules);
    }

    /**
     * 字符串大于查询。
     *
     * @param  mixed  ...$rules  附加到字段上的 Laravel 验证规则
     */
    public static function gt(...$rules): array
    {
        return static::string(QueryableType::Gt, ...$rules);
    }

    /**
     * 数值大于查询。
     *
     * @param  mixed  ...$rules  附加到字段上的 Laravel 验证规则
     */
    public static function gtNumeric(...$rules): array
    {
        return static::number(QueryableType::GtNumeric, ...$rules);
    }

    /**
     * 字符串大于等于查询。
     *
     * @param  mixed  ...$rules  附加到字段上的 Laravel 验证规则
     */
    public static function gte(...$rules): array
    {
        return static::string(QueryableType::Gte, ...$rules);
    }

    /**
     * 数值大于等于查询。
     *
     * @param  mixed  ...$rules  附加到字段上的 Laravel 验证规则
     */
    public static function gteNumeric(...$rules): array
    {
        return static::number(QueryableType::GteNumeric, ...$rules);
    }

    /**
     * 字符串 IN 查询。
     *
     * @param  string  $type  请求值的 Laravel 类型规则，默认 `array`
     * @param  mixed  ...$rules  附加到字段上的 Laravel 验证规则
     */
    public static function in($type = 'array', ...$rules): array
    {
        return static::rule(QueryableType::In, $type, ...$rules);
    }

    /**
     * 数值 IN 查询。
     *
     * @param  string  $type  请求值的 Laravel 类型规则，默认 `array`
     * @param  mixed  ...$rules  附加到字段上的 Laravel 验证规则
     */
    public static function inNumeric($type = 'array', ...$rules): array
    {
        return static::rule(QueryableType::InNumeric, $type, ...$rules);
    }

    /**
     * 模糊查询。
     *
     * @param  mixed  ...$rules  附加到字段上的 Laravel 验证规则
     */
    public static function like(...$rules): array
    {
        return static::string(QueryableType::Like, ...$rules);
    }

    /**
     * 字符串小于查询。
     *
     * @param  mixed  ...$rules  附加到字段上的 Laravel 验证规则
     */
    public static function lt(...$rules): array
    {
        return static::string(QueryableType::Lt, ...$rules);
    }

    /**
     * 数值小于查询。
     *
     * @param  mixed  ...$rules  附加到字段上的 Laravel 验证规则
     */
    public static function ltNumeric(...$rules): array
    {
        return static::number(QueryableType::LtNumeric, ...$rules);
    }

    /**
     * 字符串小于等于查询。
     *
     * @param  mixed  ...$rules  附加到字段上的 Laravel 验证规则
     */
    public static function lte(...$rules): array
    {
        return static::string(QueryableType::Lte, ...$rules);
    }

    /**
     * 数值小于等于查询。
     *
     * @param  mixed  ...$rules  附加到字段上的 Laravel 验证规则
     */
    public static function lteNumeric(...$rules): array
    {
        return static::number(QueryableType::LteNumeric, ...$rules);
    }

    /**
     * 智能搜索查询。
     *
     * $fields 支持 `id,name` 或 `id|name`。
     *
     * @param  string  $fields  参与智能搜索的字段列表
     * @param  mixed  ...$rules  附加到字段上的 Laravel 验证规则
     */
    public static function ns(string $fields, ...$rules): array
    {
        return static::string(QueryableType::Ns->asRule($fields), ...$rules);
    }

    /**
     * 区间查询。
     *
     * 接收 `start,end` 格式的区间值
     *
     * @param  mixed  ...$rules  附加到字段上的 Laravel 验证规则
     */
    public static function range(...$rules): array
    {
        return static::string(QueryableType::Range, ...$rules);
    }

    /**
     * 数值区间查询。
     *
     * 接收 `start,end` 格式的区间值
     *
     * @param  mixed  ...$rules  附加到字段上的 Laravel 验证规则
     */
    public static function rangeNumeric(...$rules): array
    {
        return static::string(QueryableType::RangeNumeric, ...$rules);
    }

    /**
     * 前缀匹配查询。
     *
     * @param  mixed  ...$rules  附加到字段上的 Laravel 验证规则
     */
    public static function startsWith(...$rules): array
    {
        return static::string(QueryableType::StartsWith, ...$rules);
    }

    /**
     * 数值字段查询。
     *
     * @param  Queryable|QueryableType|null  $queryableRule  查询规则，默认 `= value`
     * @param  mixed  ...$rules  附加到字段上的 Laravel 验证规则
     */
    protected static function number(
        Queryable|QueryableType|null $queryableRule = null,
        ...$rules
    ): array {
        return static::rule($queryableRule ?? QueryableType::EqNumeric, 'numeric', ...$rules);
    }

    /**
     * 组合一条完整的 Queryable 验证规则。
     *
     * @param  Queryable  $queryableRule  查询规则
     * @param  mixed  ...$rules  附加到字段上的 Laravel 验证规则
     * @return array<int, mixed>
     */
    protected static function rule(
        Queryable|QueryableType $queryableRule,
        ...$rules
    ): array {
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
     *
     * 未指定查询规则时，默认使用包含匹配（`LIKE %value%`）。
     *
     * @param  Queryable|QueryableType|null  $queryableRule  查询规则，默认 `LIKE %value%`
     * @param  mixed  ...$rules  附加到字段上的 Laravel 验证规则
     */
    protected static function string(
        Queryable|QueryableType|null $queryableRule = null,
        ...$rules
    ): array {
        return static::rule($queryableRule ?? QueryableType::Like, 'string', ...$rules);
    }
}
