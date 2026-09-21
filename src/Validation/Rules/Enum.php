<?php

declare(strict_types=1);

namespace Pin\Validation\Rules;

use BackedEnum;
use Override;
use TypeError;

/**
 * 枚举值验证
 */
class Enum extends ValidationRule
{
    protected string $message = 'validation.enum';

    /**
     * @param  class-string<BackedEnum>  $enum  枚举类
     */
    public function __construct(public protected(set) string $enum)
    {
    }

    /**
     * 验证枚举值
     */
    #[Override]
    protected function handle(string $attribute, mixed $value): bool
    {
        try {
            return $this->enum::tryFrom($value) !== null;
        } catch (TypeError) {
            return false;
        }
    }
}
