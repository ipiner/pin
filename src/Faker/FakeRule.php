<?php

declare(strict_types=1);

namespace Pin\Faker;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Fake 数据生成规则
 *
 * 用于定义 fake 数据生成器及其参数
 */
readonly class FakeRule implements ValidationRule
{
    /**
     * 创建 FakeRule
     */
    public function __construct(
        protected string|Closure $generator,
        protected array $parameters = [],
    ) {
    }

    /**
     * 获取 generator
     */
    public function generator(): string|Closure
    {
        return $this->generator;
    }

    /**
     * 获取指定位置参数。
     *
     * @template T
     *
     * @param  int  $index  参数下标
     * @param  T  $default  默认值
     * @return mixed|T
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
     * 当前规则仅用于挂载元数据，不进行实际验证，始终视为验证通过。
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
    }
}
