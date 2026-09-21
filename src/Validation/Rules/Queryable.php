<?php

declare(strict_types=1);

namespace Pin\Validation\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Override;
use Pin\Models\Queryable\QueryableType;
use Stringable;

/**
 * 查询类型标记
 */
class Queryable implements Stringable, ValidationRule
{
    /**
     * 查询类型
     */
    public string $type;

    public function __construct(string|QueryableType $type)
    {
        $this->type = is_string($type) ? $type : $type->value;
    }

    /**
     * 转换为查询规则字符串
     */
    #[Override]
    public function __toString(): string
    {
        return 'q:'.$this->type;
    }

    /**
     * 查询标记直接通过验证
     */
    #[Override]
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
    }
}
