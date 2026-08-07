<?php

declare(strict_types=1);

namespace Pin\Validation\Rules;

/**
 * Enum 枚举验证
 */
class Enum extends ValidationRule
{
    /**
     * 构造函数
     *
     * @param  class-string  $enum  验证的枚举类
     */
    public function __construct(public protected(set) string $enum)
    {
        $this->message('validation.enum');
    }

    /**
     * 执行验证
     */
    protected function handle(string $attribute, mixed $value): bool
    {
        return $this->enum::tryFrom($value) !== null;
    }
}
