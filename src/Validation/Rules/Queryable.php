<?php

declare(strict_types=1);

namespace Pin\Validation\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Pin\Models\Queryable\QueryableType;

class Queryable implements ValidationRule
{
    /**
     * 查询类型
     */
    public string $type;

    /**
     * @param  string  $type  查询类型
     */
    public function __construct(string|QueryableType $type)
    {
        $this->type = is_string($type) ? $type : $type->value;
    }

    /**
     * 查询规则转化为字符串
     */
    public function __toString(): string
    {
        return 'q:'.$this->type;
    }

    /**
     * 当前规则仅用于挂载查询元数据，不进行实际验证。
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
    }
}
