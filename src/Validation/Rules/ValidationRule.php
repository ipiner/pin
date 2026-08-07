<?php

declare(strict_types=1);

namespace Pin\Validation\Rules;

use Closure;

/**
 * 自定义验证规则
 */
abstract class ValidationRule implements \Illuminate\Contracts\Validation\ValidationRule
{
    /**
     * 验证失败提示信息
     */
    protected string $message;

    /**
     * 执行验证
     */
    abstract protected function handle(string $attribute, mixed $value): bool;

    /**
     * 验证入口
     *
     * @param  string  $attribute  当前验证字段
     * @param  mixed  $value  当前字段值
     * @param  Closure(string):void  $fail  验证失败回调
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->handle($attribute, $value)) {
            $fail(__($this->message));
        }
    }

    /**
     * 设置验证失败消息
     */
    public function message(string $message): static
    {
        $this->message = $message;

        return $this;
    }
}
