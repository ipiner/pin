<?php

declare(strict_types=1);

namespace Pin\Password\Concerns;

use Closure;
use Pin\Errors\IError;

/**
 * 密码验证处理
 */
trait HandlesValidation
{
    /**
     * 验证错误集合
     *
     * @var array<int, string>
     */
    protected array $errors = [];

    /**
     * 当前待验证的密码值
     */
    protected string $value;

    /**
     * 验证入口
     *
     * @param  string  $attribute  当前验证字段名
     * @param  mixed  $value  当前待验证值
     * @param  Closure  $fail  Laravel 验证失败回调
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $this->value = (string) $value;

        if (! $this->passes()) {
            foreach ($this->errors as $code => $message) {
                $fail($this->withErrorCode ? $code.'|'.$message : $message);
            }
        }
    }

    /**
     * 检查是否通过验证
     */
    protected function passes(): bool
    {
        $check = [
            $this->validateMinLength(),
            $this->validateMaxLength(),
            $this->validateWhitespace(),
            $this->validateMaxSequentialCharacters(),
            $this->validateMaxRepeatedCharacters(),
        ];

        // 字符类型组合校验
        if ($this->requiredCharacterTypes > 1) {
            $check[] = $this->validateRequiredCharacterTypes();
        } else {
            $check = array_merge($check, [
                $this->validateNumbers(),
                $this->validateLetters(),
                $this->validateLowercase(),
                $this->validateUppercase(),
                $this->validateMixedCase(),
                $this->validateSymbols(),
            ]);
        }

        return empty(array_filter(array_merge($check)));
    }

    /**
     * 验证正则规则
     *
     * @param  string  $pattern  正则表达式
     * @param  IError  $error  错误定义
     */
    protected function matchPattern(string $pattern, IError $error): int
    {
        return $this->value === '' || preg_match($pattern, $this->value)
            ? 0
            : $this->addError($error);
    }

    /**
     * 添加验证错误
     *
     * @param  IError  $error  错误定义
     * @param  array<string, mixed>  $replacements  错误消息替换参数
     */
    protected function addError(IError $error, array $replacements = []): int
    {
        $code = $error->code();
        $this->errors[$code] = $error->message($replacements);

        return $code;
    }
}
