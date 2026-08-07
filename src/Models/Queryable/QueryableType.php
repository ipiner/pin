<?php

declare(strict_types=1);

namespace Pin\Models\Queryable;

use Pin\Validation\Rules\Queryable;

/**
 * 查询操作类型
 *
 * 小写表示按字符串语义比较，大写表示按数值语义比较。
 */
enum QueryableType: string
{
    /**
     * 字符串等于
     */
    case Eq = 'eq';

    /**
     * 精确等于
     */
    case EqNumeric = 'EQ';

    /**
     * 模糊查询
     */
    case Like = 'like';

    /**
     * 查询指定前缀
     */
    case StartsWith = 'startsWith';

    /**
     * 查询指定后缀
     */
    case EndsWith = 'endsWith';

    /**
     * 字符串大于
     */
    case Gt = 'gt';

    /**
     * 数值大于
     */
    case GtNumeric = 'GT';

    /**
     * 字符串大于或等于
     */
    case Gte = 'gte';

    /**
     * 数值大于或等于
     */
    case GteNumeric = 'GTE';

    /**
     * 字符串小于
     */
    case Lt = 'lt';

    /**
     * 数值小于
     */
    case LtNumeric = 'LT';

    /**
     * 字符串小于或等于
     */
    case Lte = 'lte';

    /**
     * 数值小于或等于
     */
    case LteNumeric = 'LTE';

    /**
     * 字符串 in
     */
    case In = 'in';

    /**
     * 数值 in
     */
    case InNumeric = 'IN';

    /**
     * 字符串区间查询
     */
    case Range = 'range';

    /**
     * 数值区间查询
     */
    case RangeNumeric = 'RANGE';

    /**
     * 智能查询
     *
     * 适合一个输入框同时查多个字段：
     * 数字走精确查询，文本走模糊查询。
     *
     * 示例：ns:id|name
     */
    case Ns = 'ns';

    /**
     * 解析查询类型表达式
     *
     * 支持把 `ns:id|name`、`ns,id,name` 这类写法统一拆成：
     * 查询类型 + 关联字段列表。
     *
     * @return array{0: QueryableType, 1: string[]|null}
     */
    public static function parse(string|QueryableType $value): array
    {
        if ($value instanceof QueryableType) {
            return [$value, null];
        }

        $value = str_replace([':', ',', '|', ','], ',', $value);

        if (! str_contains($value, ',')) {
            return [self::from($value), null];
        }

        $parts = explode(',', $value);
        $type = array_shift($parts);

        return [self::from($type), $parts];
    }

    /**
     * 转成验证规则使用的查询表达式
     */
    public function asRule(?string $columns = null): Queryable
    {
        $type = $columns
            ? $this->value.','.str_replace('|', ',', $columns)
            : $this->value;

        return new Queryable($type);
    }

    /**
     * 取比较类查询对应的 SQL 操作符
     */
    public function comparisonSymbol(): ?string
    {
        return match ($this) {
            self::Gt, self::GtNumeric => '>',
            self::Gte, self::GteNumeric => '>=',
            self::Lt, self::LtNumeric => '<',
            self::Lte, self::LteNumeric => '<=',
            default => null,
        };
    }

    /**
     * 是否为 in 查询
     */
    public function isIn(): bool
    {
        return in_array($this, [self::In, self::InNumeric]);
    }

    /**
     * 是否为模糊查询
     */
    public function isLike(): bool
    {
        return in_array($this, [self::Like, self::StartsWith, self::EndsWith]);
    }

    /**
     * 是否按数值语义处理
     */
    public function isNumeric(): bool
    {
        return str_ends_with($this->name, 'Numeric');
    }

    /**
     * 是否为区间查询
     */
    public function isRange(): bool
    {
        return in_array($this, [self::Range, self::RangeNumeric]);
    }
}
