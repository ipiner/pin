<?php

declare(strict_types=1);

namespace Pin\Faker;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Override;

/**
 * 测试数据生成规则
 */
readonly class FakeRule implements ValidationRule
{
    /**
     * 构造函数
     */
    public function __construct(
        protected string|Closure $generator,
        protected array $parameters = [],
    ) {
    }

    /**
     * 获取生成器
     */
    public function generator(): string|Closure
    {
        return $this->generator;
    }

    /**
     * 获取指定位置参数。
     */
    public function parameter(int $index, mixed $default = null): mixed
    {
        return $this->parameters[$index] ?? $default;
    }

    /**
     * 获取全部参数
     */
    public function parameters(): array
    {
        return $this->parameters;
    }

    /**
     * 跳过验证
     */
    #[Override]
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
    }
}
