<?php

declare(strict_types=1);

namespace Pin\Validation\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule as ValidationRuleContract;
use Override;

/**
 * 验证规则基类
 */
abstract class ValidationRule implements ValidationRuleContract
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
     * 执行验证
     *
     * @param  Closure(string): mixed  $fail  失败回调
     */
    #[Override]
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
